<?php

namespace App\Services;

use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Enums\PatientImportBatchStatus;
use App\Exports\PatientTemplateExport;
use App\Jobs\ProcessPatientImportJob;
use App\Models\Campaign;
use App\Models\Patient;
use App\Models\PatientEligibilityStatus;
use App\Models\PatientImportBatch;
use App\Models\PatientImportLog;
use App\Models\PatientStage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PatientImportService
{
    public const AUDIT_UPLOADED = 'patient.import.uploaded';

    public const AUDIT_APPROVED = 'patient.import.approved';

    public function __construct(
        private readonly PatientService $patientService,
        private readonly CampaignWorkbookImportParser $workbookParser,
    ) {}

    public function uploadFile(
        UploadedFile $file,
        User $user,
        ?int $campaignId = null,
        ?string $notes = null
    ): PatientImportBatch {
        if (! $campaignId) {
            throw new \InvalidArgumentException(__('patients.import.messages.campaign_required'));
        }

        $storedName = Str::uuid().'.'.$file->getClientOriginalExtension();

        $batch = PatientImportBatch::create([
            'campaign_id' => $campaignId,
            'file_name' => $storedName,
            'original_file_name' => $file->getClientOriginalName(),
            'status' => PatientImportBatchStatus::Uploaded,
            'imported_by' => $user->id,
            'notes' => $notes,
        ]);

        $file->storeAs('patient-imports/'.$batch->id, $storedName, 'local');

        if (config('patient_import.sync_processing', true)) {
            ProcessPatientImportJob::dispatchSync($batch);
        } else {
            ProcessPatientImportJob::dispatch($batch);
        }

        return $batch->fresh();
    }

    public function processBatch(PatientImportBatch $batch): void
    {
        $batch->update([
            'status' => PatientImportBatchStatus::Processing,
            'failure_reason' => null,
        ]);

        $path = $batch->storagePath();

        if (! Storage::disk('local')->exists($path)) {
            $this->rejectBatch($batch, __('patients.import.messages.file_missing'));

            return;
        }

        $fullPath = Storage::disk('local')->path($path);
        $batch->logs()->delete();

        if (! $batch->campaign_id) {
            $this->rejectBatch($batch, __('patients.import.messages.campaign_required'));

            return;
        }

        $sheetNames = $this->workbookParser->sheetNames($fullPath);

        if ($this->workbookParser->isCampaignWorkbook($sheetNames)) {
            $this->rejectBatch($batch, __('patients.import.messages.workbook_not_supported', [
                'sheets' => implode(', ', $sheetNames),
                'columns' => $this->templateColumnsLabel(),
                'required' => $this->requiredColumnsLabel(),
            ]));

            return;
        }

        if (! $this->processTemplateImport($batch, $fullPath)) {
            return;
        }

        $this->refreshBatchCounts($batch);

        $batch->update(['status' => PatientImportBatchStatus::Review]);
    }

    /**
     * @param  list<list<mixed>>  $rows
     * @param  list<string>  $header
     * @return list<array{row_number: int, data: array<string, mixed>}>
     */
    public function parseFile(array $rows, array $header): array
    {
        $parsed = [];

        for ($index = 1; $index < count($rows); $index++) {
            $rowNumber = $index + 1;
            $data = $this->mapRow($header, $rows[$index]);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $parsed[] = [
                'row_number' => $rowNumber,
                'data' => $data,
            ];
        }

        return $parsed;
    }

    /**
     * @param  list<array{row_number: int, data: array<string, mixed>}>  $parsedRows
     */
    public function validateRows(PatientImportBatch $batch, array $parsedRows): void
    {
        $campaignMap = $this->campaignMap();
        $eligibilityMap = PatientEligibilityStatus::query()->active()->pluck('id', 'code');
        $stageMap = PatientStage::query()->active()->pluck('id', 'code');

        foreach ($parsedRows as $row) {
            $errors = $this->validateRowData(
                $row['data'],
                $batch,
                $campaignMap,
                $eligibilityMap,
                $stageMap
            );

            $resolved = $this->resolveRowReferences(
                $row['data'],
                $batch,
                $campaignMap,
                $eligibilityMap,
                $stageMap
            );

            PatientImportLog::create([
                'batch_id' => $batch->id,
                'row_number' => $row['row_number'],
                'patient_name' => $row['data']['patient_name'] ?? null,
                'file_number' => filled($row['data']['file_number'] ?? null) ? $row['data']['file_number'] : null,
                'validation_errors' => $errors,
                'is_valid' => count($errors) === 0,
                'is_duplicate' => false,
                'raw_data' => array_merge($row['data'], $resolved),
            ]);
        }
    }

    public function detectDuplicates(PatientImportBatch $batch): void
    {
        $logs = $batch->logs()->orderBy('row_number')->get();
        $seenInFile = [];
        $seenNamesInFile = [];

        foreach ($logs as $log) {
            if (! $log->is_valid) {
                continue;
            }

            $campaignId = $log->raw_data['resolved_campaign_id'] ?? null;
            $fileNumber = $log->file_number;

            if ($campaignId === null) {
                continue;
            }

            if (filled($fileNumber)) {
                $key = Str::lower($fileNumber);

                if (isset($seenInFile[$key])) {
                    $this->markDuplicate($log, __('patients.import.messages.duplicate_in_file', [
                        'field' => 'file_number',
                        'row' => $seenInFile[$key],
                    ]));

                    continue;
                }

                $seenInFile[$key] = $log->row_number;

                if (Patient::query()->where('file_number', $fileNumber)->exists()) {
                    $this->markDuplicate($log, __('patients.import.messages.duplicate_in_database', [
                        'file_number' => $fileNumber,
                    ]));
                }

                continue;
            }

            if (! filled($log->patient_name)) {
                continue;
            }

            $nameKey = $campaignId.'|name|'.Str::lower(trim($log->patient_name));

            if (isset($seenNamesInFile[$nameKey])) {
                $this->markDuplicate($log, __('patients.import.messages.duplicate_in_file', [
                    'field' => 'patient_name',
                    'row' => $seenNamesInFile[$nameKey],
                ]));

                continue;
            }

            $seenNamesInFile[$nameKey] = $log->row_number;

            if (Patient::query()
                ->where('campaign_id', $campaignId)
                ->whereRaw('LOWER(patient_name) = ?', [Str::lower(trim($log->patient_name))])
                ->exists()) {
                $this->markDuplicate($log, __('patients.import.messages.duplicate_name_in_database', [
                    'name' => $log->patient_name,
                ]));
            }
        }
    }

    public function approveImport(PatientImportBatch $batch, User $user): int
    {
        if (! $batch->status?->isApprovable()) {
            throw new \InvalidArgumentException(__('patients.import.messages.not_reviewable'));
        }

        $imported = 0;

        DB::transaction(function () use ($batch, $user, &$imported): void {
            $batch->update([
                'status' => PatientImportBatchStatus::Approved,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            $logs = $batch->logs()
                ->where('is_valid', true)
                ->where('is_duplicate', false)
                ->whereNull('patient_id')
                ->orderBy('row_number')
                ->get();

            foreach ($logs as $log) {
                $data = $log->raw_data;

                $eligibilityStatusId = $data['resolved_eligibility_status_id']
                    ?? PatientEligibilityStatus::query()->active()->where('code', 'accepted')->value('id')
                    ?? PatientEligibilityStatus::query()->active()->ordered()->value('id');

                $patientName = trim((string) ($data['patient_name'] ?? ''));

                if ($patientName === '') {
                    $patientName = __('patients.import.defaults.unnamed_patient', ['row' => $log->row_number]);
                }

                $dateOfBirth = filled($data['date_of_birth'] ?? null)
                    ? (string) $data['date_of_birth']
                    : (string) config('patient_import.default_date_of_birth', '2000-01-01');

                $gender = filled($data['gender'] ?? null)
                    ? (string) $data['gender']
                    : (string) config('patient_import.default_gender', Gender::Male->value);

                $patient = $this->patientService->createPatient([
                    'campaign_id' => $data['resolved_campaign_id'],
                    'patient_name' => $patientName,
                    'file_number' => filled($data['file_number'] ?? null) ? $data['file_number'] : null,
                    'date_of_birth' => $dateOfBirth,
                    'gender' => $gender,
                    'height_cm' => filled($data['height_cm'] ?? null) ? $data['height_cm'] : null,
                    'weight_kg' => filled($data['weight_kg'] ?? null) ? $data['weight_kg'] : null,
                    'contact_number' => $data['contact_number'] ?? null,
                    'eligibility_status_id' => $eligibilityStatusId,
                    'current_stage_id' => $data['resolved_stage_id'] ?? null,
                    'admission_status' => $data['admission_status'] ?? AdmissionStatus::NotAdmitted->value,
                    'surgery_day_number' => filled($data['surgery_day_number'] ?? null) ? (int) $data['surgery_day_number'] : null,
                    'rank' => filled($data['rank'] ?? null) ? (int) $data['rank'] : null,
                    'surgical_side' => $data['surgical_side'] ?? null,
                    'approval_reason' => $data['approval_reason'] ?? null,
                    'notes' => $data['patient_notes'] ?? null,
                    'screening_data' => [],
                ], $user);

                $log->update(['patient_id' => $patient->id]);
                $imported++;
            }

            $batch->update([
                'status' => PatientImportBatchStatus::Completed,
                'imported_count' => $imported,
            ]);
        });

        return $imported;
    }

    public function generateErrorFile(PatientImportBatch $batch): string
    {
        $logs = $batch->logs()
            ->where(function ($query): void {
                $query->where('is_valid', false)->orWhere('is_duplicate', true);
            })
            ->orderBy('row_number')
            ->get();

        $rows = $logs->map(function (PatientImportLog $log): array {
            $errors = collect($log->validation_errors ?? [])
                ->values()
                ->implode(' | ');

            if ($log->is_duplicate && $log->duplicate_reason) {
                $errors = trim($errors.' | '.$log->duplicate_reason, ' |');
            }

            return [
                $log->row_number,
                $log->patient_name ?? '',
                $log->raw_data['date_of_birth'] ?? '',
                $log->raw_data['gender'] ?? '',
                $log->raw_data['height_cm'] ?? '',
                $log->raw_data['weight_kg'] ?? '',
                $log->raw_data['contact_number'] ?? '',
                $errors,
                $log->is_duplicate ? __('patients.import.row_status.duplicate') : '',
            ];
        })->all();

        $exportPath = 'patient-imports/'.$batch->id.'/errors-'.now()->format('YmdHis').'.xlsx';

        Excel::store(
            new \App\Exports\PatientImportErrorsExport($rows),
            $exportPath,
            'local'
        );

        return $exportPath;
    }

    public function refreshBatchCounts(PatientImportBatch $batch): void
    {
        $total = $batch->logs()->count();
        $valid = $batch->logs()->where('is_valid', true)->where('is_duplicate', false)->count();
        $invalid = $batch->logs()->where('is_valid', false)->count();
        $duplicates = $batch->logs()->where('is_duplicate', true)->count();

        $batch->update([
            'total_rows' => $total,
            'valid_rows' => $valid,
            'invalid_rows' => $invalid,
            'duplicate_rows' => $duplicates,
        ]);
    }

    public function markBatchFailed(PatientImportBatch $batch, string $reason): void
    {
        $batch->update([
            'status' => PatientImportBatchStatus::Failed,
            'failure_reason' => $reason,
        ]);
    }

  /**
     * @param  list<mixed>  $headerRow
     * @return list<string>
     */
    private function normalizeHeaderRow(array $headerRow): array
    {
        return array_map(function (mixed $cell): string {
            $normalized = Str::snake(Str::lower(trim((string) $cell)));

            return match ($normalized) {
                'campaign', 'campaign_code' => 'campaign_code',
                'patient_name', 'name' => 'patient_name',
                'file_number', 'file_no' => 'file_number',
                'date_of_birth', 'dob', 'birth_date' => 'date_of_birth',
                'height_cm', 'height' => 'height_cm',
                'weight_kg', 'weight' => 'weight_kg',
                'contact_number', 'contact', 'mobile', 'phone' => 'contact_number',
                'eligibility_status', 'eligibility' => 'eligibility_status',
                'admission_status', 'admission' => 'admission_status',
                'stage', 'current_stage', 'stage_code' => 'stage',
                'surgery_day_number', 'surgery_day', 'day' => 'surgery_day_number',
                'rank' => 'rank',
                'surgical_side', 'side' => 'surgical_side',
                'approval_reason', 'reason' => 'approval_reason',
                'patient_notes', 'notes' => 'patient_notes',
                default => $normalized,
            };
        }, $headerRow);
    }

    /**
     * @param  list<string>  $header
     * @param  list<mixed>  $row
     * @return array<string, mixed>
     */
    private function mapRow(array $header, array $row): array
    {
        $data = [];

        foreach ($header as $index => $column) {
            if ($column === '') {
                continue;
            }

            $value = $row[$index] ?? null;
            $data[$column] = is_string($value) ? trim($value) : $value;
        }

        if (isset($data['date_of_birth'])) {
            $data['date_of_birth'] = $this->parseDate($data['date_of_birth']);
        }

        foreach (['gender', 'campaign_code', 'eligibility_status', 'admission_status', 'stage'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = Str::lower(trim($data[$field]));
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isEmptyRow(array $data): bool
    {
        return collect($data)
            ->only(config('patient_import.template_columns', []))
            ->filter(fn ($value) => filled($value))
            ->isEmpty();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  Collection<string, int>  $campaignMap
     * @param  Collection<string, int>  $eligibilityMap
     * @param  Collection<string, int>  $stageMap
     * @return list<string>
     */
    private function validateRowData(
        array $data,
        PatientImportBatch $batch,
        Collection $campaignMap,
        Collection $eligibilityMap,
        Collection $stageMap
    ): array {
        $errors = [];

        if (! $batch->campaign_id) {
            $errors[] = __('patients.import.messages.campaign_required');
        }

        $campaignCode = $data['campaign_code'] ?? null;

        if ($batch->campaign_id && filled($campaignCode)) {
            $batchCampaign = $batch->campaign ?? Campaign::query()->find($batch->campaign_id);

            if ($batchCampaign && Str::lower((string) $campaignCode) !== Str::lower((string) $batchCampaign->code)) {
                $errors[] = __('patients.import.messages.campaign_mismatch', [
                    'expected' => $batchCampaign->code,
                ]);
            }
        }

        if (! filled($data['patient_name'] ?? null)) {
            $errors[] = __('patients.import.messages.required', [
                'field' => $this->fieldLabel('patient_name'),
            ]);
        } elseif (mb_strlen(trim((string) $data['patient_name'])) > 255) {
            $errors[] = __('patients.import.messages.too_long', [
                'field' => $this->fieldLabel('patient_name'),
                'max' => 255,
            ]);
        }

        if (filled($data['gender'] ?? null) && ! in_array($data['gender'], Gender::values(), true)) {
            $errors[] = __('patients.import.messages.invalid_gender');
        }

        if (filled($data['date_of_birth'] ?? null)) {
            if ($data['date_of_birth'] === null) {
                $errors[] = __('patients.import.messages.invalid_date');
            } elseif (now()->parse($data['date_of_birth'])->isFuture()) {
                $errors[] = __('patients.import.messages.future_date');
            }
        }

        if (filled($data['height_cm'] ?? null) && ! is_numeric($data['height_cm'])) {
            $errors[] = __('patients.import.messages.invalid_number', [
                'field' => $this->fieldLabel('height_cm'),
            ]);
        } elseif (filled($data['height_cm'] ?? null)) {
            $height = (float) $data['height_cm'];
            if ($height < 20 || $height > 250) {
                $errors[] = __('patients.import.messages.out_of_range', [
                    'field' => $this->fieldLabel('height_cm'),
                    'min' => 20,
                    'max' => 250,
                ]);
            }
        }

        if (filled($data['weight_kg'] ?? null) && ! is_numeric($data['weight_kg'])) {
            $errors[] = __('patients.import.messages.invalid_number', [
                'field' => $this->fieldLabel('weight_kg'),
            ]);
        } elseif (filled($data['weight_kg'] ?? null)) {
            $weight = (float) $data['weight_kg'];
            if ($weight < 0.5 || $weight > 500) {
                $errors[] = __('patients.import.messages.out_of_range', [
                    'field' => $this->fieldLabel('weight_kg'),
                    'min' => 0.5,
                    'max' => 500,
                ]);
            }
        }

        if (filled($data['contact_number'] ?? null) && mb_strlen((string) $data['contact_number']) > 30) {
            $errors[] = __('patients.import.messages.too_long', [
                'field' => $this->fieldLabel('contact_number'),
                'max' => 30,
            ]);
        }

        if (filled($data['eligibility_status'] ?? null) && ! $eligibilityMap->has($data['eligibility_status'])) {
            $errors[] = __('patients.import.messages.invalid_eligibility', ['code' => $data['eligibility_status']]);
        }

        if (filled($data['admission_status'] ?? null) && ! in_array($data['admission_status'], AdmissionStatus::values(), true)) {
            $errors[] = __('patients.import.messages.invalid_admission');
        }

        if (filled($data['stage'] ?? null) && ! $stageMap->has($data['stage'])) {
            $errors[] = __('patients.import.messages.invalid_stage', ['code' => $data['stage']]);
        }

        return $errors;
    }

    private function fieldLabel(string $field): string
    {
        $key = 'patients.import.fields.'.$field;

        return __($key) !== $key ? __($key) : $field;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  Collection<string, int>  $campaignMap
     * @param  Collection<string, int>  $eligibilityMap
     * @param  Collection<string, int>  $stageMap
     * @return array<string, int|null>
     */
    private function resolveRowReferences(
        array $data,
        PatientImportBatch $batch,
        Collection $campaignMap,
        Collection $eligibilityMap,
        Collection $stageMap
    ): array {
        $campaignCode = $data['campaign_code'] ?? null;

        if (! filled($campaignCode) && $batch->campaign_id) {
            $campaignCode = Campaign::query()->whereKey($batch->campaign_id)->value('code');
        }

        return [
            'resolved_campaign_id' => filled($campaignCode)
                ? $campaignMap->get(Str::lower((string) $campaignCode))
                : $batch->campaign_id,
            'resolved_eligibility_status_id' => filled($data['eligibility_status'] ?? null)
                ? $eligibilityMap->get($data['eligibility_status'])
                : null,
            'resolved_stage_id' => filled($data['stage'] ?? null)
                ? $stageMap->get($data['stage'])
                : null,
        ];
    }

    /** @return Collection<string, int> */
    private function campaignMap(): Collection
    {
        return Campaign::query()
            ->whereNotNull('code')
            ->get()
            ->mapWithKeys(fn (Campaign $campaign): array => [
                Str::lower((string) $campaign->code) => $campaign->id,
            ]);
    }

    private function markDuplicate(PatientImportLog $log, string $reason): void
    {
        $log->update([
            'is_duplicate' => true,
            'duplicate_reason' => $reason,
        ]);
    }

    private function processTemplateImport(PatientImportBatch $batch, string $fullPath): bool
    {
        $sheets = Excel::toArray(new \stdClass, $fullPath);
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            $this->rejectBatch($batch, __('patients.import.messages.empty_file', [
                'required' => $this->requiredColumnsLabel(),
                'columns' => $this->templateColumnsLabel(),
            ]));

            return false;
        }

        $header = $this->normalizeHeaderRow($rows[0]);

        if ($message = $this->validateTemplateHeader($header)) {
            $this->rejectBatch($batch, $message);

            return false;
        }

        $parsedRows = $this->parseFile($rows, $header);
        $this->validateRows($batch, $parsedRows);
        $this->detectDuplicates($batch);

        return true;
    }

    private function rejectBatch(PatientImportBatch $batch, string $reason): void
    {
        $this->markBatchFailed($batch, $reason);
    }

    private function templateColumnsLabel(): string
    {
        return implode(', ', PatientTemplateExport::columnHeadings());
    }

    private function requiredColumnsLabel(): string
    {
        return implode(', ', PatientTemplateExport::requiredColumnHeadings());
    }

    /**
     * @param  list<string>  $header
     */
    private function validateTemplateHeader(array $header): ?string
    {
        $expected = PatientTemplateExport::columnHeadings();
        $found = array_values(array_filter($header, fn (string $column): bool => $column !== ''));

        if ($found === []) {
            return __('patients.import.messages.header_missing', [
                'columns' => $this->templateColumnsLabel(),
                'required' => $this->requiredColumnsLabel(),
            ]);
        }

        if ($found === $expected) {
            return null;
        }

        $missing = array_values(array_diff($expected, $found));
        $extra = array_values(array_diff($found, $expected));

        if ($missing !== [] || $extra !== []) {
            return __('patients.import.messages.invalid_template', [
                'missing' => $missing === [] ? '—' : implode(', ', $missing),
                'extra' => $extra === [] ? '—' : implode(', ', $extra),
                'expected' => $this->templateColumnsLabel(),
                'found' => implode(', ', $found),
                'required' => $this->requiredColumnsLabel(),
            ]);
        }

        return __('patients.import.messages.invalid_template_order', [
            'expected' => $this->templateColumnsLabel(),
            'found' => implode(', ', $found),
        ]);
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return now()->parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
