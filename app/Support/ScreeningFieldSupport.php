<?php

namespace App\Support;

use App\Models\CtFindingOption;
use App\Models\ExpectationPostCiOption;
use App\Models\MriFindingOption;

final class ScreeningFieldSupport
{
    /**
     * @return array<string, string>
     */
    public static function optionsFromKey(string $configKey): array
    {
        return collect(config("patient_clinical.{$configKey}", []))
            ->mapWithKeys(fn (string $label, string $code): array => [$code => __($label)])
            ->all();
    }

    public static function optionLabel(string $code, string $configKey): string
    {
        $options = config("patient_clinical.{$configKey}", []);

        return isset($options[$code]) ? __($options[$code]) : $code;
    }

    /**
     * @param  array<string, string>  $defaultOptions
     * @param  array<string, mixed>  $fieldDefinition
     * @return array{selected: list<int|string>, custom: list<string>}
     */
    public static function normalizeExpandableChecklist(
        mixed $input,
        array $defaultOptions = [],
        array $fieldDefinition = []
    ): array {
        if (self::usesSettingsOptions($fieldDefinition)) {
            return self::normalizeSettingsExpandableChecklist($input);
        }

        if (is_string($input) && filled(trim($input))) {
            return ['selected' => [], 'custom' => [trim($input)]];
        }

        if (! is_array($input)) {
            return ['selected' => [], 'custom' => []];
        }

        $selected = collect($input['selected'] ?? [])
            ->map(fn ($value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $custom = collect($input['custom'] ?? [])
            ->map(fn ($value): string => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return ['selected' => $selected, 'custom' => $custom];
    }

    /**
     * @return array{selected: list<int>, custom: list<string>}
     */
    public static function normalizeSettingsExpandableChecklist(mixed $input): array
    {
        if (! is_array($input)) {
            return ['selected' => [], 'custom' => []];
        }

        $selected = collect($input['selected'] ?? [])
            ->map(function ($value): int {
                if (is_numeric($value)) {
                    return (int) $value;
                }

                $code = trim((string) $value);
                if ($code === '') {
                    return 0;
                }

                $id = ExpectationPostCiOption::query()->where('code', $code)->value('id');

                return $id ? (int) $id : 0;
            })
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->values()
            ->all();

        return ['selected' => $selected, 'custom' => []];
    }

    /**
     * @param  array<string, mixed>  $fieldDefinition
     */
    public static function usesSettingsOptions(array $fieldDefinition): bool
    {
        return ($fieldDefinition['settings_options'] ?? null) === 'expectation_post_ci';
    }

    /**
     * @param  array<string, string>  $defaultOptions
     * @param  array<string, mixed>  $fieldDefinition
     * @return array{selected: list<int|string>, custom: list<string>}
     */
    public static function resolveExpandableChecklistForForm(
        mixed $saved,
        array $defaultOptions = [],
        array $fieldDefinition = []
    ): array {
        return self::normalizeExpandableChecklist($saved, $defaultOptions, $fieldDefinition);
    }

    /**
     * @param  array<string, mixed>  $fieldDefinition
     */
    public static function hasExpandableChecklistContent(mixed $value, array $fieldDefinition = []): bool
    {
        $data = self::normalizeExpandableChecklist($value, [], $fieldDefinition);

        return $data['selected'] !== [] || $data['custom'] !== [];
    }

    /**
     * @param  array<string, mixed>  $fieldDefinition
     */
    public static function presentExpandableChecklist(mixed $value, array $fieldDefinition = []): string
    {
        $data = self::normalizeExpandableChecklist($value, [], $fieldDefinition);
        $labels = [];

        if (self::usesSettingsOptions($fieldDefinition)) {
            foreach ($data['selected'] as $id) {
                $labels[] = self::expectationPostCiOptionLabel($id);
            }

            return $labels === [] ? '—' : implode(', ', $labels);
        }

        $optionsKey = (string) ($fieldDefinition['options_key'] ?? 'expectations_post_ci_options');

        foreach ($data['selected'] as $code) {
            $labels[] = self::optionLabel((string) $code, $optionsKey);
        }

        foreach ($data['custom'] as $text) {
            $labels[] = $text;
        }

        return $labels === [] ? '—' : implode(', ', $labels);
    }

    public static function expectationPostCiOptionLabel(int|string $value): string
    {
        if (is_numeric($value)) {
            $name = ExpectationPostCiOption::query()->find((int) $value)?->name;

            return $name ?? (string) $value;
        }

        $code = trim((string) $value);
        $name = ExpectationPostCiOption::query()->where('code', $code)->value('name');

        return $name ?? self::optionLabel($code, 'expectations_post_ci_options');
    }

    /**
     * @return array{pre_op_request: ?string, general_condition: ?string}
     */
    public static function normalizeMedicalHistoryScreening(mixed $input): array
    {
        if (is_string($input) && filled(trim($input))) {
            return [
                'pre_op_request' => null,
                'general_condition' => null,
            ];
        }

        if (! is_array($input)) {
            return ['pre_op_request' => null, 'general_condition' => null];
        }

        $preOp = filled($input['pre_op_request'] ?? null) ? (string) $input['pre_op_request'] : null;
        $general = filled($input['general_condition'] ?? null) ? (string) $input['general_condition'] : null;

        return [
            'pre_op_request' => $preOp,
            'general_condition' => $general,
        ];
    }

    /**
     * @return array{pre_op_request: string, general_condition: string}
     */
    public static function resolveMedicalHistoryForForm(mixed $saved): array
    {
        $normalized = self::normalizeMedicalHistoryScreening($saved);

        return [
            'pre_op_request' => (string) ($normalized['pre_op_request'] ?? ''),
            'general_condition' => (string) ($normalized['general_condition'] ?? ''),
        ];
    }

    public static function hasMedicalHistoryScreeningContent(mixed $value): bool
    {
        if (is_string($value) && filled(trim($value))) {
            return true;
        }

        $data = self::normalizeMedicalHistoryScreening($value);

        return filled($data['pre_op_request'] ?? null) || filled($data['general_condition'] ?? null);
    }

    public static function presentMedicalHistoryScreening(mixed $value): string
    {
        if (is_string($value) && filled(trim($value))) {
            return trim($value);
        }

        $data = self::normalizeMedicalHistoryScreening($value);
        $lines = [];

        if (filled($data['pre_op_request'] ?? null)) {
            $lines[] = __('workflow.fields.medical_history_pre_op_request').': '
                .self::optionLabel((string) $data['pre_op_request'], 'medical_history_pre_op_request_options');
        }

        if (filled($data['general_condition'] ?? null)) {
            $lines[] = __('workflow.fields.medical_history_general_condition').': '
                .self::optionLabel((string) $data['general_condition'], 'medical_history_general_condition_options');
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function selectOptionLabel(mixed $value, array $fieldDefinition): string
    {
        $code = trim((string) ($value ?? ''));
        if ($code === '') {
            return '—';
        }

        $options = $fieldDefinition['options'] ?? [];
        if (isset($options[$code])) {
            return (string) $options[$code];
        }

        if (isset($fieldDefinition['options_key'])) {
            return self::optionLabel($code, (string) $fieldDefinition['options_key']);
        }

        return $code;
    }

    /**
     * @return array{right: array{ct: list<int>, mri: list<int>}, left: array{ct: list<int>, mri: list<int>}}
     */
    public static function emptyImagingFindings(): array
    {
        return [
            'right' => ['ct' => [], 'mri' => []],
            'left' => ['ct' => [], 'mri' => []],
        ];
    }

    /**
     * @return list<int>
     */
    private static function normalizeImagingOptionIds(mixed $values): array
    {
        return collect(is_array($values) ? $values : [])
            ->map(fn ($value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{right: array{ct: list<int>, mri: list<int>}, left: array{ct: list<int>, mri: list<int>}}
     */
    public static function normalizeImagingFindings(mixed $input): array
    {
        if (is_string($input) && filled(trim($input))) {
            return self::emptyImagingFindings();
        }

        if (! is_array($input)) {
            return self::emptyImagingFindings();
        }

        return [
            'right' => [
                'ct' => self::normalizeImagingOptionIds($input['right']['ct'] ?? []),
                'mri' => self::normalizeImagingOptionIds($input['right']['mri'] ?? []),
            ],
            'left' => [
                'ct' => self::normalizeImagingOptionIds($input['left']['ct'] ?? []),
                'mri' => self::normalizeImagingOptionIds($input['left']['mri'] ?? []),
            ],
        ];
    }

    /**
     * @return array{right: array{ct: list<int>, mri: list<int>}, left: array{ct: list<int>, mri: list<int>}}
     */
    public static function resolveImagingFindingsForForm(mixed $saved): array
    {
        return self::normalizeImagingFindings($saved);
    }

    public static function hasImagingFindingsContent(mixed $value): bool
    {
        if (is_string($value) && filled(trim($value))) {
            return true;
        }

        $data = self::normalizeImagingFindings($value);

        foreach (['right', 'left'] as $ear) {
            if (($data[$ear]['ct'] ?? []) !== [] || ($data[$ear]['mri'] ?? []) !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<int>  $ids
     * @return list<string>
     */
    private static function imagingOptionLabels(array $ids, string $modelClass): array
    {
        if ($ids === []) {
            return [];
        }

        /** @var class-string<CtFindingOption|MriFindingOption> $modelClass */
        return $modelClass::query()
            ->whereIn('id', $ids)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public static function presentImagingFindings(mixed $value): string
    {
        if (is_string($value) && filled(trim($value))) {
            return trim($value);
        }

        $data = self::normalizeImagingFindings($value);
        $sections = [];

        foreach (['right', 'left'] as $ear) {
            $ctLabels = self::imagingOptionLabels($data[$ear]['ct'] ?? [], CtFindingOption::class);
            $mriLabels = self::imagingOptionLabels($data[$ear]['mri'] ?? [], MriFindingOption::class);

            if ($ctLabels === [] && $mriLabels === []) {
                continue;
            }

            $lines = [__('workflow.fields.imaging_ear_'.$ear)];

            if ($ctLabels !== []) {
                $lines[] = __('workflow.fields.ct_findings').': '.implode(', ', $ctLabels);
            }

            if ($mriLabels !== []) {
                $lines[] = __('workflow.fields.mri_findings').': '.implode(', ', $mriLabels);
            }

            $sections[] = implode("\n", $lines);
        }

        return $sections === [] ? '—' : implode("\n\n", $sections);
    }
}
