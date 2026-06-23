<?php

namespace App\Support;

use App\Services\LookupService;

class PatientClinicalFieldRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function screeningFields(): array
    {
        return $this->resolveFieldDefinitions(config('patient_clinical.screening_fields', []));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getStageFields(string $stageCode): array
    {
        if ($stageCode === 'pre_operation') {
            return $this->preOperationFields();
        }

        $fields = config("patient_clinical.stage_fields.{$stageCode}", []);

        return $this->resolveFieldDefinitions($fields);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function preOperationFields(): array
    {
        $keys = config('patient_clinical.pre_operation_field_keys', []);
        $screening = $this->screeningFields();
        $fields = [];

        foreach ($keys as $key) {
            if (isset($screening[$key])) {
                $fields[$key] = $screening[$key];
            }
        }

        return $fields;
    }

    /**
     * @return array<string, array{label: string, color: string, background: string}>
     */
    public function phases(): array
    {
        $phases = [];

        foreach (config('patient_clinical.phases', []) as $code => $phase) {
            $phases[$code] = [
                'label' => __($phase['label']),
                'color' => $phase['color'],
                'background' => $phase['background'],
            ];
        }

        return $phases;
    }

    public function phaseForStage(string $stageCode): string
    {
        if ($stageCode === 'pre_operation') {
            $fields = $this->preOperationFields();
        } else {
            $fields = config("patient_clinical.stage_fields.{$stageCode}", []);
        }

        $first = reset($fields);

        return is_array($first) ? (string) ($first['phase'] ?? 'pre_op') : 'pre_op';
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array<string, array<string, mixed>>
     */
    private function resolveFieldDefinitions(array $fields): array
    {
        $resolved = [];

        foreach ($fields as $key => $definition) {
            $resolved[$key] = [
                ...$definition,
                'label' => __($definition['label'] ?? $key),
                'required' => $definition['required'] ?? false,
            ];

            if (isset($definition['options']) && is_array($definition['options'])) {
                $resolved[$key]['options'] = collect($definition['options'])
                    ->mapWithKeys(fn (string $label, string $value): array => [
                        $value => str_contains($label, '.') ? __($label) : $label,
                    ])
                    ->all();
            }

            if (isset($definition['options_key']) && is_string($definition['options_key'])) {
                $resolved[$key]['options'] = ScreeningFieldSupport::optionsFromKey($definition['options_key']);
            }

            if (($definition['type'] ?? '') === 'expandable_checklist'
                && ($definition['settings_options'] ?? null) === 'expectation_post_ci') {
                $lookup = app(LookupService::class);
                $resolved[$key]['options'] = $lookup->getExpectationPostCiOptions()
                    ->mapWithKeys(fn ($option): array => [$option->id => $option->name])
                    ->all();
                $resolved[$key]['allow_add_options'] = false;
            }

            if (($definition['type'] ?? '') === 'medical_history_screening') {
                $resolved[$key]['lists'] = [
                    'pre_op_request' => [
                        'label' => __('workflow.fields.medical_history_pre_op_request'),
                        'options' => ScreeningFieldSupport::optionsFromKey('medical_history_pre_op_request_options'),
                    ],
                    'general_condition' => [
                        'label' => __('workflow.fields.medical_history_general_condition'),
                        'options' => ScreeningFieldSupport::optionsFromKey('medical_history_general_condition_options'),
                    ],
                ];
            }

            if (($definition['type'] ?? '') === 'imaging_findings') {
                $lookup = app(LookupService::class);
                $resolved[$key]['ct_options'] = $lookup->getCtFindingOptions()
                    ->mapWithKeys(fn ($option): array => [$option->id => $option->name])
                    ->all();
                $resolved[$key]['mri_options'] = $lookup->getMriFindingOptions()
                    ->mapWithKeys(fn ($option): array => [$option->id => $option->name])
                    ->all();
            }
        }

        return $resolved;
    }
}
