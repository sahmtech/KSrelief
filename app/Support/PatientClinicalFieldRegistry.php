<?php

namespace App\Support;

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
        $fields = config("patient_clinical.stage_fields.{$stageCode}", []);

        return $this->resolveFieldDefinitions($fields);
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
        $fields = config("patient_clinical.stage_fields.{$stageCode}", []);
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
                    ->mapWithKeys(fn (string $label, string $value): array => [$value => $label])
                    ->all();
            }
        }

        return $resolved;
    }
}
