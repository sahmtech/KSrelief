<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Support\PatientClinicalFieldRegistry;

class PatientClinicalProfileService
{
    public function __construct(
        private readonly MedicalRecordService $recordService,
        private readonly PatientClinicalFieldRegistry $fieldRegistry,
    ) {}

    /**
     * @return array{
     *     phases: array<string, array{label: string, color: string, background: string, items: list<array{label: string, value: string, source: string}>}>,
     *     records_by_stage: \Illuminate\Support\Collection,
     * }
     */
    public function buildProfile(Patient $patient): array
    {
        $phases = $this->recordService->clinicalPhases();
        $grouped = collect($phases)->mapWithKeys(fn (array $phase, string $code): array => [
            $code => [
                ...$phase,
                'items' => [],
            ],
        ])->all();

        foreach ($this->fieldRegistry->screeningFields() as $key => $definition) {
            $value = $patient->screening($key);
            if (! filled($value)) {
                continue;
            }

            $phase = $definition['phase'] ?? 'screening';
            $grouped[$phase]['items'][] = [
                'label' => $definition['label'],
                'value' => (string) $value,
                'source' => __('patients.clinical.source_screening'),
                'type' => $definition['type'] ?? 'text',
            ];
        }

        if (filled($patient->approval_reason)) {
            $grouped['screening']['items'][] = [
                'label' => __('patients.fields.approval_reason'),
                'value' => $patient->approval_reason,
                'source' => __('patients.clinical.source_patient'),
                'type' => 'text',
            ];
        }

        if (filled($patient->surgical_side)) {
            $grouped['pre_op']['items'][] = [
                'label' => __('patients.fields.surgical_side'),
                'value' => $patient->surgicalSideLabel(),
                'source' => __('patients.clinical.source_patient'),
                'type' => 'text',
            ];
        }

        $recordsByStage = $this->recordService->getLatestRecordsByStage($patient);

        foreach ($recordsByStage as $record) {
            $stageCode = $record->stage?->code ?? '';
            $fields = $this->fieldRegistry->getStageFields($stageCode);

            foreach ($fields as $key => $definition) {
                $value = $record->field($key);
                if (! filled($value)) {
                    continue;
                }

                $phase = $definition['phase'] ?? 'pre_op';
                $grouped[$phase]['items'][] = [
                    'label' => $definition['label'],
                    'value' => $this->formatFieldValue($value, $definition, $record),
                    'source' => $record->stage?->name ?? __('workflow.medical_records'),
                    'type' => $definition['type'] ?? 'text',
                ];
            }
        }

        return [
            'phases' => $grouped,
            'records_by_stage' => $recordsByStage,
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function formatFieldValue(mixed $value, array $definition, MedicalRecord $record): string
    {
        if (($definition['type'] ?? '') === 'member_select' && is_numeric($value)) {
            $member = $record->patient?->campaign?->staffMembers?->firstWhere('id', (int) $value);

            return $member?->full_name ?? (string) $value;
        }

        return (string) $value;
    }
}
