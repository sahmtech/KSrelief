<?php

namespace App\Services;

use App\Exports\PatientCampaignExport;
use App\Exports\PatientExportSheet;
use App\Models\Campaign;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Support\MedicalRecordExportFormatter;
use App\Support\PatientClinicalFieldRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientExportService
{
    public function __construct(
        private readonly PatientClinicalFieldRegistry $fieldRegistry,
    ) {}

    /**
     * @return Collection<int, PatientStage>
     */
    public function exportableStages(): Collection
    {
        $stageCodes = array_keys(config('patient_clinical.stage_fields', []));

        return PatientStage::query()
            ->whereIn('code', $stageCodes)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * @param  list<string>  $stageCodes
     */
    public function download(Campaign $campaign, array $stageCodes): BinaryFileResponse
    {
        $sheets = [
            $this->buildPatientsSheet($campaign),
        ];

        foreach ($this->resolveStageCodes($stageCodes) as $stageCode) {
            $sheet = $this->buildStageRecordsSheet($campaign, $stageCode);

            if ($sheet !== null) {
                $sheets[] = $sheet;
            }
        }

        $filename = sprintf(
            'patients-%s-%s.xlsx',
            Str::slug($campaign->code ?: $campaign->name),
            now()->format('Ymd-His')
        );

        return Excel::download(new PatientCampaignExport($sheets), $filename);
    }

    private function buildPatientsSheet(Campaign $campaign): PatientExportSheet
    {
        $headings = [
            'file_number',
            'patient_name',
            'date_of_birth',
            'gender',
            'height_cm',
            'weight_kg',
            'contact_number',
            'eligibility_status',
            'admission_status',
            'current_stage',
            'surgery_day_number',
            'rank',
            'surgical_side',
            'approval_reason',
            'patient_notes',
        ];

        $patients = Patient::query()
            ->with(['eligibilityStatus', 'currentStage'])
            ->where('campaign_id', $campaign->id)
            ->orderBy('rank')
            ->orderBy('patient_name')
            ->get();

        $rows = $patients->map(fn (Patient $patient): array => [
            $patient->file_number,
            $patient->patient_name,
            $patient->date_of_birth?->format('Y-m-d'),
            $patient->gender?->value,
            $patient->height_cm,
            $patient->weight_kg,
            $patient->contact_number,
            $patient->eligibilityStatus?->code,
            $patient->admission_status?->value,
            $patient->currentStage?->code,
            $patient->surgery_day_number,
            $patient->rank,
            $patient->surgical_side,
            $patient->approval_reason,
            $patient->notes,
        ])->all();

        return new PatientExportSheet(__('patients.export.sheets.patients'), $headings, $rows);
    }

    private function buildStageRecordsSheet(Campaign $campaign, string $stageCode): ?PatientExportSheet
    {
        $stage = PatientStage::query()->where('code', $stageCode)->first();

        if (! $stage) {
            return null;
        }

        $fieldDefinitions = $this->fieldRegistry->getStageFields($stageCode);

        if ($fieldDefinitions === []) {
            return null;
        }

        $headings = [
            'file_number',
            'patient_name',
            'record_sequence',
            'record_date',
            'record_notes',
            'submitted_by',
        ];

        foreach ($fieldDefinitions as $fieldKey => $definition) {
            $headings[] = $fieldKey;
        }

        $records = MedicalRecord::query()
            ->with(['patient', 'submitter'])
            ->whereHas('patient', fn ($query) => $query->where('campaign_id', $campaign->id))
            ->where('stage_id', $stage->id)
            ->orderBy('patient_id')
            ->orderBy('record_date')
            ->orderBy('id')
            ->get();

        $sequenceByPatient = [];
        $rows = [];

        foreach ($records as $record) {
            $sequenceByPatient[$record->patient_id] = ($sequenceByPatient[$record->patient_id] ?? 0) + 1;

            $row = [
                $record->patient?->file_number,
                $record->patient?->patient_name,
                $sequenceByPatient[$record->patient_id],
                $record->record_date?->format('Y-m-d'),
                $record->notes,
                $record->submitter?->name,
            ];

            foreach ($fieldDefinitions as $fieldKey => $definition) {
                $row[] = MedicalRecordExportFormatter::format(
                    $fieldKey,
                    $record->field($fieldKey),
                    $definition
                );
            }

            $rows[] = $row;
        }

        $title = Str::limit($stage->displayName(), 31, '');

        return new PatientExportSheet($title, $headings, $rows);
    }

    /**
     * @param  list<string>  $stageCodes
     * @return list<string>
     */
    private function resolveStageCodes(array $stageCodes): array
    {
        $allowed = $this->exportableStages()->pluck('code')->all();

        if ($stageCodes === [] || in_array('all', $stageCodes, true)) {
            return $allowed;
        }

        return array_values(array_intersect($stageCodes, $allowed));
    }
}
