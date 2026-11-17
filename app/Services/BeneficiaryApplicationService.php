<?php

namespace App\Services;

use App\Enums\BeneficiaryApplicationStatus;
use App\Enums\Gender;
use App\Models\BeneficiaryApplication;
use App\Models\BeneficiaryApplicationAttachment;
use App\Models\Campaign;
use App\Models\Patient;
use App\Models\PatientEligibilityStatus;
use App\Models\User;
use App\Services\Push\DoctorPushNotifier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BeneficiaryApplicationService
{
    public function __construct(
        private readonly PatientService $patientService,
        private readonly PatientAccessService $patientAccessService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, list<UploadedFile>>  $filesByField
     */
    public function submit(array $data, array $filesByField, ?string $ip = null, ?string $userAgent = null): BeneficiaryApplication
    {
        $nationalNumber = (string) $data['national_number'];

        $duplicate = BeneficiaryApplication::query()
            ->pending()
            ->where('national_number', $nationalNumber)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'national_number' => [__('beneficiary_registration.messages.duplicate_pending')],
            ]);
        }

        return DB::transaction(function () use ($data, $filesByField, $ip, $userAgent): BeneficiaryApplication {
            $extraType = (int) $data['extra_type'];
            $email = $data['extra']['email'] ?? $data['email'] ?? null;

            $application = BeneficiaryApplication::create([
                'status' => BeneficiaryApplicationStatus::Pending,
                'extra_type' => $extraType,
                'full_name' => trim((string) $data['name']),
                'national_number' => (string) $data['national_number'],
                'mobile' => (string) $data['mobile'],
                'email' => filled($email) ? (string) $email : null,
                'gender' => (int) $data['gender'],
                'birth_date' => $data['birth_date'],
                'country_code' => (string) $data['country'],
                'application_payload' => $this->buildPayload($data),
                'ip_address' => $ip,
                'user_agent' => $userAgent ? Str::limit($userAgent, 1000, '') : null,
            ]);

            foreach ($filesByField as $fieldKey => $files) {
                foreach ($files as $file) {
                    if ($file instanceof UploadedFile) {
                        $this->storeAttachment($application, $fieldKey, $file);
                    }
                }
            }

            $application->load('attachments');

            try {
                app(DoctorPushNotifier::class)->beneficiaryApplicationSubmitted($application);
            } catch (\Throwable) {
                // Push failures must not block submission.
            }

            return $application;
        });
    }

    public function accept(BeneficiaryApplication $application, User $reviewer, int $campaignId): Patient
    {
        if (! $application->isPending()) {
            throw ValidationException::withMessages([
                'application' => [__('beneficiary_registration.messages.not_pending')],
            ]);
        }

        $this->patientAccessService->assertCanAccessCampaign($reviewer, $campaignId);

        return DB::transaction(function () use ($application, $reviewer, $campaignId): Patient {
            $application = BeneficiaryApplication::query()->lockForUpdate()->findOrFail($application->id);

            if (! $application->isPending()) {
                throw ValidationException::withMessages([
                    'application' => [__('beneficiary_registration.messages.not_pending')],
                ]);
            }

            $eligibilityId = PatientEligibilityStatus::query()
                ->active()
                ->where('code', config('beneficiary_registration.patient_mapping.eligibility_status_code', 'accepted'))
                ->value('id');

            if (! $eligibilityId) {
                $eligibilityId = PatientEligibilityStatus::query()->active()->ordered()->value('id');
            }

            $patientData = $this->mapToPatientData($application, $campaignId, (int) $eligibilityId);

            $patient = $this->patientService->createPatient(
                $patientData,
                $reviewer,
                [],
                null,
                notifyDoctors: true,
            );

            $photoAttachment = $application->attachments()->where('field_key', 'photo')->first();
            if ($photoAttachment && $photoAttachment->existsOnDisk()) {
                $this->copyPhotoToPatient($patient, $photoAttachment);
            }

            foreach ($application->attachments()->where('field_key', '!=', 'photo')->get() as $attachment) {
                $this->copyAttachmentToPatient($patient, $attachment, $reviewer);
            }

            $application->update([
                'status' => BeneficiaryApplicationStatus::Accepted,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
                'accepted_patient_id' => $patient->id,
                'accepted_campaign_id' => $campaignId,
            ]);

            try {
                app(DoctorPushNotifier::class)->beneficiaryApplicationAccepted(
                    $application->fresh(['acceptedCampaign', 'acceptedPatient']),
                    $reviewer
                );
            } catch (\Throwable) {
            }

            return $patient->fresh([
                'campaign',
                'eligibilityStatus',
                'currentStage',
                'attachments',
            ]);
        });
    }

    public function reject(BeneficiaryApplication $application, User $reviewer, string $reason): BeneficiaryApplication
    {
        if (! $application->isPending()) {
            throw ValidationException::withMessages([
                'application' => [__('beneficiary_registration.messages.not_pending')],
            ]);
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages([
                'rejection_reason' => [__('beneficiary_registration.messages.reason_required')],
            ]);
        }

        $application->update([
            'status' => BeneficiaryApplicationStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $application->fresh(['reviewer']);
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function campaignsForReviewer(User $user): array
    {
        $allowed = $this->patientAccessService->allowedCampaignIds($user);

        $query = Campaign::query()->orderBy('name');

        if ($allowed !== null) {
            if ($allowed === []) {
                return [];
            }
            $query->whereIn('id', $allowed);
        }

        return $query->get(['id', 'name'])
            ->map(fn (Campaign $campaign): array => [
                'id' => $campaign->id,
                'name' => $campaign->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function formMeta(): array
    {
        $extraTypes = [];
        foreach (config('beneficiary_registration.extra_types', []) as $id => $meta) {
            $extraTypes[] = [
                'id' => (int) $id,
                'code' => $meta['code'],
                'label' => __($meta['label']),
                'visible_steps' => $meta['visible_steps'],
                'extra_fields' => collect($meta['extra_fields'] ?? [])
                    ->map(fn (array $field, string $key): array => [
                        'key' => $key,
                        'type' => $field['type'],
                        'required' => (bool) ($field['required'] ?? false),
                        'label' => __($field['label']),
                        'accept' => $field['accept'] ?? null,
                        'max_files' => $field['max_files'] ?? null,
                    ])
                    ->values()
                    ->all(),
            ];
        }

        $marital = [];
        foreach (config('beneficiary_registration.marital_status', []) as $id => $labels) {
            $marital[] = [
                'id' => (int) $id,
                'label_male' => $labels['male'],
                'label_female' => $labels['female'],
            ];
        }

        return [
            'extra_types' => $extraTypes,
            'genders' => [
                ['id' => 1, 'code' => 'male', 'label' => __('beneficiary_registration.options.male')],
                ['id' => 2, 'code' => 'female', 'label' => __('beneficiary_registration.options.female')],
            ],
            'countries' => collect(config('beneficiary_registration.countries', []))
                ->map(fn (string $label, string $code): array => ['code' => $code, 'label' => $label])
                ->values()
                ->all(),
            'default_country' => config('beneficiary_registration.default_country', 'sa'),
            'marital_status' => $marital,
            'health_status' => [
                ['id' => 1, 'label' => __('beneficiary_registration.options.health_healthy')],
                ['id' => 2, 'label' => __('beneficiary_registration.options.health_sick')],
            ],
            'diseases' => collect(config('beneficiary_registration.diseases', []))
                ->map(fn (string $label, int|string $id): array => ['id' => (int) $id, 'label' => $label])
                ->values()
                ->all(),
            'disease_types' => collect(config('beneficiary_registration.disease_types', []))
                ->map(fn (string $label, int|string $id): array => ['id' => (int) $id, 'label' => $label])
                ->values()
                ->all(),
            'treatment_types' => collect(config('beneficiary_registration.treatment_types', []))
                ->map(fn (string $label, int|string $id): array => ['id' => (int) $id, 'label' => $label])
                ->values()
                ->all(),
            'statuses' => collect(BeneficiaryApplicationStatus::cases())
                ->map(fn (BeneficiaryApplicationStatus $status): array => [
                    'code' => $status->value,
                    'label' => $status->label(),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildPayload(array $data): array
    {
        return [
            'extra_type' => (int) $data['extra_type'],
            'name' => trim((string) $data['name']),
            'national_number' => (string) $data['national_number'],
            'gender' => (int) $data['gender'],
            'mobile' => (string) $data['mobile'],
            'country' => (string) $data['country'],
            'birth_date' => (string) $data['birth_date'],
            'marital_status' => (int) $data['marital_status'],
            'extra' => [
                'case_description' => trim((string) ($data['extra']['case_description'] ?? '')),
                'email' => filled($data['extra']['email'] ?? null) ? (string) $data['extra']['email'] : null,
            ],
            'health' => [
                'health_status' => (int) $data['health']['health_status'],
                'handicapped' => (bool) ($data['health']['handicapped'] ?? false),
                'handicapped_percentage' => filled($data['health']['handicapped_percentage'] ?? null)
                    ? (int) $data['health']['handicapped_percentage']
                    : null,
                'diseases' => array_values($data['health']['diseases'] ?? []),
                'health_notes' => trim((string) ($data['health']['health_notes'] ?? '')),
            ],
        ];
    }

    private function storeAttachment(
        BeneficiaryApplication $application,
        string $fieldKey,
        UploadedFile $file,
    ): BeneficiaryApplicationAttachment {
        $disk = (string) config('beneficiary_registration.disk', 'local');
        $storedName = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs(
            $application->id.'/'.$fieldKey,
            $storedName,
            $disk
        );

        return BeneficiaryApplicationAttachment::create([
            'beneficiary_application_id' => $application->id,
            'field_key' => $fieldKey,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => (int) $file->getSize(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapToPatientData(BeneficiaryApplication $application, int $campaignId, int $eligibilityId): array
    {
        $genderCode = config("beneficiary_registration.gender.{$application->gender}", 'male');
        $payload = $application->application_payload ?? [];
        $mapping = config('beneficiary_registration.patient_mapping.screening_keys', []);

        $screening = [
            $mapping['national_number'] ?? 'beneficiary_national_number' => $application->national_number,
            $mapping['country'] ?? 'beneficiary_country' => $application->country_code,
            $mapping['marital_status'] ?? 'beneficiary_marital_status' => data_get($payload, 'marital_status'),
            $mapping['extra_type'] ?? 'beneficiary_extra_type' => $application->extra_type,
            $mapping['case_description'] ?? 'beneficiary_case_description' => data_get($payload, 'extra.case_description'),
            $mapping['health_status'] ?? 'beneficiary_health_status' => data_get($payload, 'health.health_status'),
            $mapping['handicapped'] ?? 'beneficiary_handicapped' => data_get($payload, 'health.handicapped'),
            $mapping['handicapped_percentage'] ?? 'beneficiary_handicapped_percentage' => data_get($payload, 'health.handicapped_percentage'),
            $mapping['diseases'] ?? 'beneficiary_diseases' => data_get($payload, 'health.diseases'),
            $mapping['health_notes'] ?? 'beneficiary_health_notes' => data_get($payload, 'health.health_notes'),
            $mapping['application_uuid'] ?? 'beneficiary_application_uuid' => $application->uuid,
        ];

        $notesParts = array_filter([
            data_get($payload, 'extra.case_description'),
            data_get($payload, 'health.health_notes'),
        ]);

        return [
            'campaign_id' => $campaignId,
            'patient_name' => $application->full_name,
            'date_of_birth' => $application->birth_date?->format('Y-m-d'),
            'gender' => $genderCode === 'female' ? Gender::Female->value : Gender::Male->value,
            'contact_number' => $application->mobile,
            'eligibility_status_id' => $eligibilityId,
            'notes' => $notesParts !== [] ? implode("\n\n", $notesParts) : null,
            'screening_data' => $screening,
        ];
    }

    private function copyPhotoToPatient(Patient $patient, BeneficiaryApplicationAttachment $attachment): void
    {
        $disk = $attachment->disk();
        $contents = Storage::disk($disk)->get($attachment->path);
        if ($contents === null) {
            return;
        }

        $extension = pathinfo($attachment->original_name, PATHINFO_EXTENSION) ?: 'jpg';
        $target = 'patient-photos/'.$patient->id.'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($target, $contents);
        $patient->update(['photo' => $target]);
    }

    private function copyAttachmentToPatient(
        Patient $patient,
        BeneficiaryApplicationAttachment $attachment,
        User $user,
    ): void {
        $disk = $attachment->disk();
        if (! Storage::disk($disk)->exists($attachment->path)) {
            return;
        }

        $extension = pathinfo($attachment->original_name, PATHINFO_EXTENSION) ?: 'bin';
        $storedName = Str::uuid().'.'.$extension;
        $targetPath = 'patient-attachments/'.$patient->id.'/'.$storedName;

        Storage::disk('local')->put(
            $targetPath,
            Storage::disk($disk)->get($attachment->path)
        );

        \App\Models\PatientAttachment::create([
            'patient_id' => $patient->id,
            'original_name' => $attachment->field_key.' — '.$attachment->original_name,
            'file_name' => $storedName,
            'file_type' => $attachment->mime,
            'file_size' => $attachment->size,
            'storage_path' => $targetPath,
            'uploaded_by' => $user->id,
            'notes' => 'From beneficiary application #'.$attachment->beneficiary_application_id,
        ]);
    }
}
