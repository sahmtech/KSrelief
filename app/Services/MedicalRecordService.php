<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
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
     * Get stage-specific field definitions for building dynamic forms.
     *
     * @return array<string, array{label: string, type: string, required: bool}>
     */
    public function getStageFields(string $stageCode): array
    {
        return match ($stageCode) {
            'admission' => [
                'coordinator'        => ['label' => __('workflow.fields.coordinator'), 'type' => 'member_select', 'member_role' => 'coordinator', 'required' => false],
                'admission_notes'    => ['label' => __('workflow.fields.admission_notes'), 'type' => 'textarea', 'required' => false],
                'initial_assessment' => ['label' => __('workflow.fields.initial_assessment'), 'type' => 'textarea', 'required' => false],
            ],
            'anesthesia' => [
                'attending_doctor' => ['label' => __('workflow.fields.attending_doctor'), 'type' => 'member_select', 'member_role' => 'doctor', 'required' => false],
                'weight'           => ['label' => __('workflow.fields.weight'), 'type' => 'number', 'required' => false],
                'anesthesia_notes' => ['label' => __('workflow.fields.anesthesia_notes'), 'type' => 'textarea', 'required' => false],
                'readiness_status' => ['label' => __('workflow.fields.readiness_status'), 'type' => 'text', 'required' => false],
                'comments'         => ['label' => __('workflow.fields.comments'), 'type' => 'textarea', 'required' => false],
            ],
            'operation' => [
                'operation_date'  => ['label' => __('workflow.fields.operation_date'), 'type' => 'date', 'required' => false],
                'start_time'      => ['label' => __('workflow.fields.start_time'), 'type' => 'time', 'required' => false],
                'end_time'        => ['label' => __('workflow.fields.end_time'), 'type' => 'time', 'required' => false],
                'surgeon'         => ['label' => __('workflow.fields.surgeon'), 'type' => 'member_select', 'member_role' => 'doctor', 'required' => false],
                'specialist'      => ['label' => __('workflow.fields.specialist'), 'type' => 'member_select', 'member_role' => 'specialist', 'required' => false],
                'side'            => ['label' => __('workflow.fields.side'), 'type' => 'select', 'required' => false, 'options' => ['left' => __('workflow.sides.left'), 'right' => __('workflow.sides.right'), 'bilateral' => __('workflow.sides.bilateral')]],
                'electrode_type'  => ['label' => __('workflow.fields.electrode_type'), 'type' => 'text', 'required' => false],
                'insertion_type'  => ['label' => __('workflow.fields.insertion_type'), 'type' => 'text', 'required' => false],
                'operation_notes' => ['label' => __('workflow.fields.operation_notes'), 'type' => 'textarea', 'required' => false],
            ],
            'post_operation' => [
                'post_op_xray'    => ['label' => __('workflow.fields.post_op_xray'), 'type' => 'text', 'required' => false],
                'findings'        => ['label' => __('workflow.fields.findings'), 'type' => 'textarea', 'required' => false],
                'complications'   => ['label' => __('workflow.fields.complications'), 'type' => 'textarea', 'required' => false],
                'recommendations' => ['label' => __('workflow.fields.recommendations'), 'type' => 'textarea', 'required' => false],
            ],
            'activation' => [
                'coordinator'       => ['label' => __('workflow.fields.coordinator'), 'type' => 'member_select', 'member_role' => 'coordinator', 'required' => false],
                'activation_date'   => ['label' => __('workflow.fields.activation_date'), 'type' => 'date', 'required' => false],
                'activation_result' => ['label' => __('workflow.fields.activation_result'), 'type' => 'text', 'required' => false],
                'comments'          => ['label' => __('workflow.fields.comments'), 'type' => 'textarea', 'required' => false],
            ],
            'rehab_education' => [
                'specialist'        => ['label' => __('workflow.fields.specialist'), 'type' => 'member_select', 'member_role' => 'specialist', 'required' => false],
                'session_date'      => ['label' => __('workflow.fields.session_date'), 'type' => 'date', 'required' => false],
                'education_notes'   => ['label' => __('workflow.fields.education_notes'), 'type' => 'textarea', 'required' => false],
                'rehab_plan'        => ['label' => __('workflow.fields.rehab_plan'), 'type' => 'textarea', 'required' => false],
                'outcome'           => ['label' => __('workflow.fields.outcome'), 'type' => 'text', 'required' => false],
            ],
            default => [],
        };
    }
}
