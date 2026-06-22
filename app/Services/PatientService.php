<?php

namespace App\Services;

use App\Enums\AdmissionStatus;
use App\Enums\PatientRecordStatus;
use App\Models\Campaign;
use App\Models\Patient;
use App\Models\PatientAttachment;
use App\Models\PatientStage;
use App\Models\User;
use App\Support\RecordCodeGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientService
{
    public function __construct(
        private readonly PatientWorkflowService $workflowService,
        private readonly RecordCodeGenerator $codeGenerator,
    ) {}

    public const AUDIT_CREATED = 'patient.created';

    public const AUDIT_UPDATED = 'patient.updated';

    public const AUDIT_DELETED = 'patient.deleted';

    public const AUDIT_ELIGIBILITY_CHANGED = 'patient.eligibility.changed';

    public const AUDIT_STAGE_CHANGED = 'patient.stage.changed';

    public const AUDIT_ATTACHMENT_UPLOADED = 'patient.attachment.uploaded';

    public function createPatient(
        array $data,
        User $user,
        array $attachments = [],
        ?UploadedFile $photo = null
    ): Patient {
        return DB::transaction(function () use ($data, $user, $attachments, $photo): Patient {
            $prepared = $this->preparePatientData($data);

            if (! filled($prepared['file_number'])) {
                $campaign = Campaign::query()->lockForUpdate()->findOrFail($prepared['campaign_id']);
                $prepared['file_number'] = $this->codeGenerator->generatePatientFileNumber($campaign);
            }

            $patient = Patient::create([
                ...$prepared,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            if ($photo) {
                $patient->update([
                    'photo' => $this->storePhoto($patient, $photo),
                ]);
            }

            foreach ($attachments as $file) {
                if ($file instanceof UploadedFile) {
                    $this->uploadAttachment($patient, $file, $user);
                }
            }

            if ($patient->current_stage_id) {
                $this->workflowService->recordInitialStage(
                    $patient,
                    $user,
                    __('workflow.messages.initial_stage')
                );
            }

            return $patient->load([
                'campaign',
                'eligibilityStatus',
                'currentStage',
                'attachments',
                'creator',
            ]);
        });
    }

    public function updatePatient(
        Patient $patient,
        array $data,
        User $user,
        array $newAttachments = [],
        ?UploadedFile $photo = null,
        bool $removePhoto = false
    ): Patient {
        return DB::transaction(function () use ($patient, $data, $user, $newAttachments, $photo, $removePhoto): Patient {
            $previousEligibilityId = $patient->eligibility_status_id;

            $prepared = $this->preparePatientData($data, $patient);
            unset($prepared['current_stage_id']);

            $patient->update([
                ...$prepared,
                'updated_by' => $user->id,
            ]);

            if ($removePhoto) {
                $this->deletePhoto($patient);
            } elseif ($photo) {
                $patient->update([
                    'photo' => $this->storePhoto($patient, $photo),
                ]);
            }

            if ($previousEligibilityId !== $patient->eligibility_status_id) {
                // Future: dispatch audit log event self::AUDIT_ELIGIBILITY_CHANGED
            }

            foreach ($newAttachments as $file) {
                if ($file instanceof UploadedFile) {
                    $this->uploadAttachment($patient, $file, $user);
                }
            }

            return $patient->fresh([
                'campaign',
                'eligibilityStatus',
                'currentStage',
                'attachments',
                'creator',
                'updater',
            ]);
        });
    }

    public function deletePatient(Patient $patient): void
    {
        DB::transaction(function () use ($patient): void {
            $this->deletePhotoFile($patient->photo);
            $patient->attachments->each(fn (PatientAttachment $attachment) => $this->removeAttachment($attachment));

            $patient->delete();
        });
    }

    private function storePhoto(Patient $patient, UploadedFile $file): string
    {
        $this->deletePhotoFile($patient->photo);

        return $file->store('patient-photos/'.$patient->id, 'public');
    }

    private function deletePhoto(Patient $patient): void
    {
        $this->deletePhotoFile($patient->photo);

        $patient->update(['photo' => null]);
    }

    private function deletePhotoFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function uploadAttachment(
        Patient $patient,
        UploadedFile $file,
        User $user,
        ?string $notes = null
    ): PatientAttachment {
        $storedName = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs(
            'patient-attachments/'.$patient->id,
            $storedName,
            'local'
        );

        $attachment = PatientAttachment::create([
            'patient_id' => $patient->id,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $storedName,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'storage_path' => $path,
            'uploaded_by' => $user->id,
            'notes' => $notes,
        ]);

        return $attachment->load('uploader');
    }

    public function removeAttachment(PatientAttachment $attachment): void
    {
        DB::transaction(function () use ($attachment): void {
            $attachment->deleteStoredFile();
            $attachment->delete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePatientData(array $data, ?Patient $patient = null): array
    {
        $dob = $data['date_of_birth'];
        $ages = $this->calculateAge($dob);

        $currentStageId = $data['current_stage_id'] ?? null;

        if (! $currentStageId && ! $patient) {
            $currentStageId = PatientStage::query()->active()->default()->value('id')
                ?? PatientStage::query()->active()->ordered()->value('id');
        }

        if ($patient) {
            $currentStageId = $patient->current_stage_id;
        }

        return [
            'campaign_id' => $data['campaign_id'],
            'surgery_day_number' => filled($data['surgery_day_number'] ?? null) ? (int) $data['surgery_day_number'] : null,
            'rank' => filled($data['rank'] ?? null) ? (int) $data['rank'] : null,
            'patient_name' => $data['patient_name'],
            'file_number' => filled($data['file_number'] ?? null)
                ? $data['file_number']
                : ($patient?->file_number),
            'date_of_birth' => $dob,
            'age_years' => $ages['years'],
            'age_months' => $ages['months'],
            'gender' => $data['gender'],
            'height_cm' => filled($data['height_cm'] ?? null) ? $data['height_cm'] : null,
            'weight_kg' => filled($data['weight_kg'] ?? null) ? $data['weight_kg'] : null,
            'contact_number' => $data['contact_number'] ?? null,
            'eligibility_status_id' => $data['eligibility_status_id'],
            'approval_reason' => $data['approval_reason'] ?? null,
            'current_stage_id' => $currentStageId,
            'admission_status' => $data['admission_status'] ?? AdmissionStatus::NotAdmitted->value,
            'surgical_side' => $data['surgical_side'] ?? null,
            'notes' => $data['notes'] ?? null,
            'screening_data' => $this->extractScreeningData($data),
            'status' => $data['status'] ?? PatientRecordStatus::Active->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function extractScreeningData(array $data): array
    {
        $screening = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'screening_')) {
                $fieldKey = substr($key, 10);
                if (filled($value)) {
                    $screening[$fieldKey] = $value;
                }
            }
        }

        if (isset($data['screening_data']) && is_array($data['screening_data'])) {
            $screening = array_merge($screening, array_filter($data['screening_data'], fn ($v) => filled($v)));
        }

        return $screening;
    }

    /**
     * @return array{years: int, months: int}
     */
    public function calculateAge(string $dateOfBirth): array
    {
        $dob = now()->parse($dateOfBirth);
        $diff = $dob->diff(now());

        return [
            'years' => (int) $diff->y,
            'months' => (int) ($diff->y * 12 + $diff->m),
        ];
    }
}
