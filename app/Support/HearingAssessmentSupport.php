<?php

namespace App\Support;

final class HearingAssessmentSupport
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function types(): array
    {
        return config('hearing_assessment.types', []);
    }

    /**
     * @return list<string>
     */
    public static function typeKeys(): array
    {
        return array_keys(self::types());
    }

    /**
     * @return array{hearing_types: list<string>, assessments: array<string, mixed>}
     */
    public static function defaultPayload(): array
    {
        return [
            'hearing_types' => [],
            'assessments' => [],
        ];
    }

    /**
     * @return array{hearing_types: list<string>, assessments: array<string, mixed>}
     */
    public static function normalize(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultPayload();
        }

        $types = collect($input['hearing_types'] ?? [])
            ->map(fn (mixed $value): string => trim((string) $value))
            ->filter(fn (string $value): bool => array_key_exists($value, self::types()))
            ->unique()
            ->values()
            ->all();

        $assessments = [];

        foreach ($types as $type) {
            $assessments[$type] = self::normalizeAssessment(
                $type,
                is_array($input['assessments'][$type] ?? null) ? $input['assessments'][$type] : []
            );
        }

        return [
            'hearing_types' => $types,
            'assessments' => $assessments,
        ];
    }

    /**
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    public static function mergeInto(array $base, mixed $input): array
    {
        $normalized = self::normalize($input);

        return array_merge($base, $normalized);
    }

    public static function hasContent(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        $data = self::normalize($value);

        if ($data['hearing_types'] !== []) {
            return true;
        }

        foreach ($data['assessments'] as $assessment) {
            if (self::assessmentHasContent($assessment)) {
                return true;
            }
        }

        return false;
    }

    public static function present(mixed $value): string
    {
        $data = self::normalize($value);
        $sections = [];

        foreach ($data['hearing_types'] as $type) {
            $assessment = $data['assessments'][$type] ?? [];
            if (! self::assessmentHasContent($assessment)) {
                continue;
            }

            $lines = [self::typeLabel($type)];
            $meta = self::types()[$type] ?? [];
            $layout = (string) ($meta['layout'] ?? '');

            $lines = array_merge($lines, match ($layout) {
                'ear_options' => self::presentEarOptions($type, $assessment),
                'bc_ac_table' => self::presentBcAcTable($assessment, in_array('cm', $meta['extras'] ?? [], true)),
                'ff_table' => self::presentFfTable($assessment),
                'speech_table' => self::presentSpeechTable($assessment),
                default => [],
            });

            $sections[] = implode("\n", $lines);
        }

        return $sections === [] ? '' : implode("\n\n", $sections);
    }

    public static function typeLabel(string $type): string
    {
        $labelKey = self::types()[$type]['label_key'] ?? $type;

        return str_contains($labelKey, '.') ? __($labelKey) : $labelKey;
    }

    /**
     * @return array<string, string>
     */
    public static function earOptionsForType(string $type): array
    {
        $optionsKey = self::types()[$type]['options_key'] ?? $type;
        $options = config("hearing_assessment.ear_options.{$optionsKey}", []);

        return collect($options)
            ->mapWithKeys(fn (string $labelKey, string $code): array => [
                $code => str_contains($labelKey, '.') ? __($labelKey) : $labelKey,
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function frequencies(): array
    {
        return config('hearing_assessment.frequencies', []);
    }

    /**
     * @param  array<string, mixed>  $assessment
     */
    private static function normalizeAssessment(string $type, array $assessment): array
    {
        $layout = (string) (self::types()[$type]['layout'] ?? '');

        return match ($layout) {
            'ear_options' => self::normalizeEarOptions($assessment, $type),
            'bc_ac_table' => self::normalizeBcAcTable($assessment, self::types()[$type]['extras'] ?? []),
            'ff_table' => self::normalizeFfTable($assessment),
            'speech_table' => self::normalizeSpeechTable($assessment),
            default => [],
        };
    }

    /**
     * @return array{right: ?string, left: ?string}
     */
    private static function normalizeEarOptions(array $assessment, string $type): array
    {
        $allowed = array_keys(self::earOptionsForType($type));

        return [
            'right' => self::normalizeEarOptionValue($assessment['right'] ?? null, $allowed),
            'left' => self::normalizeEarOptionValue($assessment['left'] ?? null, $allowed),
        ];
    }

    /**
     * @param  list<string>  $allowed
     */
    private static function normalizeEarOptionValue(mixed $value, array $allowed): ?string
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        $value = trim((string) ($value ?? ''));

        return in_array($value, $allowed, true) ? $value : null;
    }

    /**
     * @param  list<string>  $extras
     * @return array<string, mixed>
     */
    private static function normalizeBcAcTable(array $assessment, array $extras): array
    {
        $result = [
            'bc' => self::normalizeFrequencyGrid($assessment['bc'] ?? []),
            'ac' => self::normalizeFrequencyGrid($assessment['ac'] ?? []),
        ];

        if (in_array('cm', $extras, true)) {
            $result['cm'] = [
                'right' => trim((string) ($assessment['cm']['right'] ?? '')),
                'left' => trim((string) ($assessment['cm']['left'] ?? '')),
            ];
        }

        return $result;
    }

    /**
     * @return array<string, array{right: string, left: string}>
     */
    private static function normalizeFrequencyGrid(mixed $input): array
    {
        $input = is_array($input) ? $input : [];
        $grid = [];

        foreach (self::frequencies() as $frequency) {
            $row = is_array($input[$frequency] ?? null) ? $input[$frequency] : [];
            $grid[$frequency] = [
                'right' => trim((string) ($row['right'] ?? '')),
                'left' => trim((string) ($row['left'] ?? '')),
            ];
        }

        return $grid;
    }

    /**
     * @return array<string, array{right: string, left: string}>
     */
    private static function normalizeFfTable(array $assessment): array
    {
        return self::normalizeFrequencyGrid($assessment['ff'] ?? []);
    }

    /**
     * @return array{srt: array{right: string, left: string}, wrs: array{right: string, left: string}}
     */
    private static function normalizeSpeechTable(array $assessment): array
    {
        return [
            'srt' => [
                'right' => trim((string) ($assessment['srt']['right'] ?? '')),
                'left' => trim((string) ($assessment['srt']['left'] ?? '')),
            ],
            'wrs' => [
                'right' => trim((string) ($assessment['wrs']['right'] ?? '')),
                'left' => trim((string) ($assessment['wrs']['left'] ?? '')),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $assessment
     */
    private static function assessmentHasContent(array $assessment): bool
    {
        foreach ($assessment as $section) {
            if (self::sectionHasContent($section)) {
                return true;
            }
        }

        return false;
    }

    private static function sectionHasContent(mixed $section): bool
    {
        if (is_string($section)) {
            return filled(trim($section));
        }

        if (! is_array($section)) {
            return false;
        }

        if (array_is_list($section) && ! isset($section['right'])) {
            return $section !== [];
        }

        foreach ($section as $value) {
            if (self::sectionHasContent($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $assessment
     * @return list<string>
     */
    private static function presentEarOptions(string $type, array $assessment): array
    {
        $options = self::earOptionsForType($type);
        $lines = [];

        foreach (['right', 'left'] as $ear) {
            $selected = $assessment[$ear] ?? null;

            if (is_array($selected)) {
                $selected = $selected[0] ?? null;
            }

            if (! filled($selected)) {
                continue;
            }

            $lines[] = __('workflow.fields.imaging_ear_'.$ear).': '.($options[$selected] ?? $selected);
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $assessment
     * @return list<string>
     */
    private static function presentBcAcTable(array $assessment, bool $includeCm): array
    {
        $lines = [];

        foreach (['bc' => 'BC', 'ac' => 'AC'] as $section => $label) {
            $sectionLines = self::presentFrequencyGrid($assessment[$section] ?? [], $label);
            array_push($lines, ...$sectionLines);
        }

        if ($includeCm) {
            $cm = is_array($assessment['cm'] ?? null) ? $assessment['cm'] : [];
            foreach (['right', 'left'] as $ear) {
                if (filled($cm[$ear] ?? null)) {
                    $lines[] = __('workflow.hearing_assessment.sections.cm').' ('.__('workflow.fields.imaging_ear_'.$ear).'): '.$cm[$ear];
                }
            }
        }

        return $lines;
    }

    /**
     * @param  array<string, array{right: string, left: string}>  $grid
     * @return list<string>
     */
    private static function presentFrequencyGrid(array $grid, string $sectionLabel): array
    {
        $lines = [];

        foreach (self::frequencies() as $frequency) {
            $row = $grid[$frequency] ?? ['right' => '', 'left' => ''];
            $parts = [];

            foreach (['right', 'left'] as $ear) {
                if (filled($row[$ear] ?? null)) {
                    $parts[] = __('workflow.fields.imaging_ear_'.$ear).': '.$row[$ear];
                }
            }

            if ($parts !== []) {
                $lines[] = "{$sectionLabel} {$frequency} Hz — ".implode(' | ', $parts);
            }
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $assessment
     * @return list<string>
     */
    private static function presentFfTable(array $assessment): array
    {
        return self::presentFrequencyGrid($assessment['ff'] ?? [], 'FF');
    }

    /**
     * @param  array<string, mixed>  $assessment
     * @return list<string>
     */
    private static function presentSpeechTable(array $assessment): array
    {
        $lines = [];

        foreach (['srt' => 'SRT', 'wrs' => 'WRS'] as $metric => $label) {
            $row = is_array($assessment[$metric] ?? null) ? $assessment[$metric] : [];
            $parts = [];

            foreach (['right', 'left'] as $ear) {
                if (filled($row[$ear] ?? null)) {
                    $parts[] = __('workflow.fields.imaging_ear_'.$ear).': '.$row[$ear];
                }
            }

            if ($parts !== []) {
                $lines[] = "{$label} — ".implode(' | ', $parts);
            }
        }

        return $lines;
    }
}
