<?php

namespace App\Support;

final class FollowUpFieldSupport
{
    /** @var list<string> */
    public const AUDIOLOGY_DEFAULT_KEYS = [
        'Impedance',
        'Ecap',
        'Magnet power',
    ];

    /** @var list<string> */
    private const AUDIOLOGY_REMOVED_KEYS = [
        'Hearing level',
        'SRT',
        'SDS',
    ];

    /**
     * @return array<string, array<string, string>>
     */
    public static function clinicalAssessmentSelectGroups(): array
    {
        return [
            'wound' => self::optionsFromKey('follow_up_wound_options'),
            'implant_bed' => self::optionsFromKey('follow_up_implant_bed_options'),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function speechAssessmentOptionGroups(): array
    {
        return [
            'communication_mood' => self::optionsFromKey('follow_up_communication_mood_options'),
            'true_word' => self::optionsFromKey('follow_up_true_word_options'),
            'phrases' => self::optionsFromKey('follow_up_phrases_options'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function optionsFromKey(string $configKey): array
    {
        return collect(config("patient_clinical.{$configKey}", []))
            ->mapWithKeys(fn (string $label, string $code): array => [
                $code => str_contains($label, '.') ? __($label) : $label,
            ])
            ->all();
    }

    /**
     * @return array{wound: ?string, implant_bed: ?string}
     */
    public static function defaultClinicalAssessment(): array
    {
        return [
            'wound' => null,
            'implant_bed' => null,
        ];
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function defaultAudiologyAssessment(): array
    {
        return array_merge(
            HearingAssessmentSupport::defaultPayload(),
            ['metrics' => ClinicalCompositeFields::defaultMetrics(self::AUDIOLOGY_DEFAULT_KEYS)]
        );
    }

    /**
     * @return array{communication_mood: ?string, true_word: ?string, phrases: ?string, cap: string, sir: string}
     */
    public static function defaultSpeechAssessment(): array
    {
        return [
            'communication_mood' => null,
            'true_word' => null,
            'phrases' => null,
            'cap' => '',
            'sir' => '',
        ];
    }

    public static function defaultNotes(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultFormPayload(): array
    {
        return [
            'clinical_assessment' => self::defaultClinicalAssessment(),
            'audiology_assessment' => self::defaultAudiologyAssessment(),
            'speech_assessment' => self::defaultSpeechAssessment(),
            'follow_up_notes' => '',
        ];
    }

    /**
     * @return array{wound: ?string, implant_bed: ?string}
     */
    public static function normalizeClinicalAssessment(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultClinicalAssessment();
        }

        if (self::isLegacyClinicalAssessment($input)) {
            $input = self::migrateLegacyClinicalAssessment($input);
        }

        return [
            'wound' => self::normalizeSelectValue($input['wound'] ?? null),
            'implant_bed' => self::normalizeSelectValue($input['implant_bed'] ?? null),
        ];
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function normalizeAudiologyAssessment(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultAudiologyAssessment();
        }

        $metricsInput = collect($input['metrics'] ?? [])
            ->reject(fn ($row): bool => is_array($row) && in_array(
                trim((string) ($row['key'] ?? '')),
                self::AUDIOLOGY_REMOVED_KEYS,
                true
            ))
            ->values()
            ->all();

        $metrics = ClinicalCompositeFields::resolveAudForForm(
            ['metrics' => $metricsInput],
            self::AUDIOLOGY_DEFAULT_KEYS,
            false
        )['metrics'];

        $metrics = collect($metrics)
            ->reject(fn (array $row): bool => in_array(
                trim((string) ($row['key'] ?? '')),
                self::AUDIOLOGY_REMOVED_KEYS,
                true
            ))
            ->values()
            ->all();

        return HearingAssessmentSupport::mergeInto([
            'metrics' => $metrics,
        ], $input);
    }

    /**
     * @return array{wound: ?string, implant_bed: ?string}
     */
    public static function resolveClinicalAssessmentForForm(mixed $saved): array
    {
        if (! is_array($saved)) {
            return self::defaultClinicalAssessment();
        }

        if (self::isLegacyClinicalAssessment($saved)) {
            return self::normalizeClinicalAssessment(self::migrateLegacyClinicalAssessment($saved));
        }

        return self::normalizeClinicalAssessment($saved);
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function resolveAudiologyAssessmentForForm(mixed $saved): array
    {
        if (! is_array($saved)) {
            return self::defaultAudiologyAssessment();
        }

        if (collect($saved['metrics'] ?? [])->contains(fn (array $row): bool => filled($row['value'] ?? null))
            || HearingAssessmentSupport::hasContent($saved)) {
            return self::normalizeAudiologyAssessment($saved);
        }

        return self::defaultAudiologyAssessment();
    }

    /**
     * @return array{communication_mood: ?string, true_word: ?string, phrases: ?string, cap: string, sir: string}
     */
    public static function normalizeSpeechAssessment(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultSpeechAssessment();
        }

        return [
            'communication_mood' => self::normalizeSelectValue($input['communication_mood'] ?? null),
            'true_word' => self::normalizeSelectValue($input['true_word'] ?? null),
            'phrases' => self::normalizeSelectValue($input['phrases'] ?? null),
            'cap' => trim((string) ($input['cap'] ?? '')),
            'sir' => trim((string) ($input['sir'] ?? '')),
        ];
    }

    public static function normalizeNotes(mixed $input): string
    {
        if (is_string($input)) {
            return trim($input);
        }

        if (! is_array($input)) {
            return '';
        }

        $lines = $input['lines'] ?? $input;

        if (! is_array($lines)) {
            return '';
        }

        return collect($lines)
            ->map(fn (mixed $line): string => trim((string) $line))
            ->filter(fn (string $line): bool => $line !== '')
            ->implode("\n");
    }

    public static function resolveNotesForForm(mixed $saved): string
    {
        return self::normalizeNotes($saved);
    }

    public static function hasClinicalAssessmentContent(mixed $value): bool
    {
        $data = self::normalizeClinicalAssessment($value);

        return filled($data['wound']) || filled($data['implant_bed']);
    }

    public static function hasAudiologyAssessmentContent(mixed $value): bool
    {
        $data = self::normalizeAudiologyAssessment($value);

        return HearingAssessmentSupport::hasContent($data)
            || collect($data['metrics'] ?? [])->contains(fn (array $row): bool => filled($row['value'] ?? null));
    }

    public static function hasSpeechAssessmentContent(mixed $value): bool
    {
        $data = self::normalizeSpeechAssessment($value);

        return filled($data['communication_mood'])
            || filled($data['true_word'])
            || filled($data['phrases'])
            || filled($data['cap'])
            || filled($data['sir']);
    }

    public static function hasNotesContent(mixed $value): bool
    {
        return filled(self::normalizeNotes($value));
    }

    public static function presentClinicalAssessment(mixed $value): string
    {
        $data = self::resolveClinicalAssessmentForForm($value);
        $groups = self::clinicalAssessmentSelectGroups();
        $lines = [];

        foreach (['wound' => __('workflow.follow_up.tables.wound'), 'implant_bed' => __('workflow.follow_up.tables.implant_bed')] as $key => $title) {
            if (! filled($data[$key] ?? null)) {
                continue;
            }

            $optionLabel = $groups[$key][$data[$key]] ?? $data[$key];
            $lines[] = "{$title}: {$optionLabel}";
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentAudiologyAssessment(mixed $value): string
    {
        $data = self::normalizeAudiologyAssessment($value);
        $sections = [];

        if ($hearingLines = HearingAssessmentSupport::present($data)) {
            $sections[] = $hearingLines;
        }

        $metricLines = collect($data['metrics'] ?? [])
            ->filter(fn (array $row): bool => filled($row['value'] ?? null))
            ->map(fn (array $row): string => ($row['key'] ?? '').': '.($row['value'] ?? ''))
            ->all();

        if ($metricLines !== []) {
            $sections[] = __('workflow.hearing_assessment.metrics_table').': '.implode(', ', $metricLines);
        }

        return $sections === [] ? '—' : implode("\n\n", $sections);
    }

    public static function presentSpeechAssessment(mixed $value): string
    {
        $data = self::normalizeSpeechAssessment($value);
        $groups = self::speechAssessmentOptionGroups();
        $lines = [];

        foreach (['communication_mood', 'true_word', 'phrases'] as $field) {
            if (! filled($data[$field])) {
                continue;
            }

            $label = __('workflow.follow_up.fields.'.$field);
            $optionLabel = $groups[$field][$data[$field]] ?? $data[$field];
            $lines[] = "{$label}: {$optionLabel}";
        }

        foreach (['cap', 'sir'] as $field) {
            if (! filled($data[$field])) {
                continue;
            }

            $lines[] = __('workflow.follow_up.fields.'.$field).': '.$data[$field];
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentNotes(mixed $value): string
    {
        $text = self::normalizeNotes($value);

        return $text === '' ? '—' : $text;
    }

    /**
     * @param  array<string, mixed>  $saved
     */
    private static function isLegacyClinicalAssessment(array $saved): bool
    {
        foreach (['wound', 'implant_bed'] as $key) {
            if (is_array($saved[$key] ?? null) && array_key_exists('metrics', $saved[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $saved
     * @return array{wound: ?string, implant_bed: ?string}
     */
    private static function migrateLegacyClinicalAssessment(array $saved): array
    {
        $result = self::defaultClinicalAssessment();

        foreach (['wound', 'implant_bed'] as $key) {
            $table = $saved[$key] ?? null;
            if (! is_array($table)) {
                continue;
            }

            $result[$key] = self::extractLegacySelectFromMetricsTable($table) ?? $result[$key];
        }

        return $result;
    }

    private static function normalizeSelectValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }

        if (! is_array($value)) {
            return null;
        }

        if (array_key_exists('metrics', $value)) {
            return self::extractLegacySelectFromMetricsTable($value);
        }

        if (filled($value['value'] ?? null) && (is_string($value['value']) || is_numeric($value['value']))) {
            return (string) $value['value'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $table
     */
    private static function extractLegacySelectFromMetricsTable(array $table): ?string
    {
        foreach ($table['metrics'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $metricKey = strtolower(trim((string) ($row['key'] ?? '')));
            $metricValue = strtolower(trim((string) ($row['value'] ?? '')));
            $candidate = $metricValue !== '' ? $metricValue : $metricKey;

            $resolved = match ($candidate) {
                'clean' => 'clean',
                'infected' => 'infected',
                'dehiscent' => 'dehiscent',
                default => null,
            };

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }
}
