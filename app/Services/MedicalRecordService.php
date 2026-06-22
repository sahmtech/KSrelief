<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Support\PatientClinicalFieldRegistry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    public function __construct(
        private readonly PatientClinicalFieldRegistry $fieldRegistry,
    ) {}

    public const AUDIT_CREATED = 'medical_record.created';

    public const AUDIT_UPDATED = 'medical_record.updated';

    public const AUDIT_DELETED = 'medical_record.deleted';

    public function createRecord(Patient $patient, array $data, User $user): MedicalRecord
    {
        return DB::transaction(function () use ($patient, $data, $user): MedicalRecord {
            $record = MedicalRecord::create([
                'patient_id'  => $patient->id,
                'stage_id'    => $data['stage_id'],
                'specialty_id' => $data['specialty_id'] ?? null,
                'record_date' => $data['record_date'],
                'fields_json' => $this->buildFieldsJson($data),
                'notes'       => $data['notes'] ?? null,
                'submitted_by' => $user->id,
            ]);

            // Future: dispatch(new AuditEvent(self::AUDIT_CREATED, $record, $user));

            return $record->load(['stage', 'submitter', 'specialty']);
        });
    }

    public function updateRecord(MedicalRecord $record, array $data, User $user): MedicalRecord
    {
        return DB::transaction(function () use ($record, $data, $user): MedicalRecord {
            $record->update([
                'stage_id'    => $data['stage_id'] ?? $record->stage_id,
                'specialty_id' => $data['specialty_id'] ?? $record->specialty_id,
                'record_date' => $data['record_date'] ?? $record->record_date,
                'fields_json' => $this->buildFieldsJson($data),
                'notes'       => $data['notes'] ?? $record->notes,
            ]);

            // Future: dispatch(new AuditEvent(self::AUDIT_UPDATED, $record, $user));

            return $record->fresh(['stage', 'submitter', 'specialty']);
        });
    }

    public function deleteRecord(MedicalRecord $record): void
    {
        DB::transaction(function () use ($record): void {
            // Future: dispatch(new AuditEvent(self::AUDIT_DELETED, $record, auth()->user()));
            $record->delete();
        });
    }

    /**
     * @return Collection<int, MedicalRecord>
     */
    public function getPatientRecords(Patient $patient): Collection
    {
        return $patient->medicalRecords()
            ->with(['stage', 'submitter', 'specialty'])
            ->orderBy('record_date', 'desc')
            ->get();
    }

    /**
     * Extract the stage-specific dynamic fields from the request data.
     * All fields prefixed with 'field_' are stored in fields_json.
     *
     * @return array<string, mixed>
     */
    private function buildFieldsJson(array $data): array
    {
        $fields = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'field_')) {
                $fieldKey = substr($key, 6);
                $fields[$fieldKey] = $value;
            }
        }

        // Also support explicit 'fields' key from API
        if (isset($data['fields']) && is_array($data['fields'])) {
            $fields = array_merge($fields, $data['fields']);
        }

        return $fields;
    }

    /**
     * @return array<string, array{label: string, type: string, required: bool}>
     */
    public function getStageFields(string $stageCode): array
    {
        return $this->fieldRegistry->getStageFields($stageCode);
    }

    public function phaseForStage(string $stageCode): string
    {
        return $this->fieldRegistry->phaseForStage($stageCode);
    }

    /**
     * @return array<string, array{label: string, color: string, background: string}>
     */
    public function clinicalPhases(): array
    {
        return $this->fieldRegistry->phases();
    }

    /**
     * @return array<string, array{label: string, type: string, required: bool}>
     */
    public function getScreeningFields(): array
    {
        return $this->fieldRegistry->screeningFields();
    }

    /**
     * Get the latest medical record per stage for a patient.
     *
     * @return Collection<int, MedicalRecord>
     */
    public function getLatestRecordsByStage(Patient $patient): Collection
    {
        return $patient->medicalRecords()
            ->with(['stage', 'submitter'])
            ->orderByDesc('record_date')
            ->orderByDesc('id')
            ->get()
            ->unique('stage_id')
            ->values();
    }
}
