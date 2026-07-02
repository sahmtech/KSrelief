<?php

namespace App\Support;

use App\Services\ClinicalSelectOptionService;

final class PostOperationFieldSupport
{
    /** @var list<string> */
    public const CLINICAL_AUD_KEYS = ['Impedance', 'E cap', 'magnet power'];

    /** @var array<string, string> */
    public const SELECT_CATEGORY_CONFIG = [
        'post_op_wound' => 'post_op_wound_options',
        'post_op_implant_bed' => 'post_op_implant_bed_options',
        'post_op_facial_nerve' => 'post_op_facial_nerve_options',
        'post_op_xray' => 'post_op_xray_options',
    ];

    /**
     * @return array<string, string>
     */
    public static function selectOptions(string $category): array
    {
        $configKey = self::SELECT_CATEGORY_CONFIG[$category] ?? null;

        return app(ClinicalSelectOptionService::class)->optionsForCategory($category, $configKey);
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function physicianAssessmentSelectGroups(): array
    {
        return [
            'wound' => self::selectOptions('post_op_wound'),
            'implant_bed' => self::selectOptions('post_op_implant_bed'),
            'facial_nerve' => self::selectOptions('post_op_facial_nerve'),
            'post_op_xray' => self::selectOptions('post_op_xray'),
        ];
    }

    /**
     * @return array{wound: ?string, implant_bed: ?string, facial_nerve: ?string, post_op_xray: ?string}
     */
    public static function defaultPhysicianAssessment(): array
    {
        return [
            'wound' => null,
            'implant_bed' => null,
            'facial_nerve' => null,
            'post_op_xray' => null,
        ];
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function defaultClinicalAud(): array
    {
        return ['metrics' => ClinicalCompositeFields::defaultMetrics(self::CLINICAL_AUD_KEYS)];
    }

    public static function normalizeNotes(mixed $input): string
    {
        return trim((string) $input);
    }

    public static function resolveNotesForForm(mixed $saved): string
    {
        return self::normalizeNotes($saved);
    }

    /**
     * @return array{wound: ?string, implant_bed: ?string, facial_nerve: ?string, post_op_xray: ?string}
     */
    public static function normalizePhysicianAssessment(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultPhysicianAssessment();
        }

        return [
            'wound' => filled($input['wound'] ?? null) ? (string) $input['wound'] : null,
            'implant_bed' => filled($input['implant_bed'] ?? null) ? (string) $input['implant_bed'] : null,
            'facial_nerve' => filled($input['facial_nerve'] ?? null) ? (string) $input['facial_nerve'] : null,
            'post_op_xray' => filled($input['post_op_xray'] ?? null) ? (string) $input['post_op_xray'] : null,
        ];
    }

    /**
     * @return array{wound: ?string, implant_bed: ?string, facial_nerve: ?string, post_op_xray: ?string}
     */
    public static function resolvePhysicianAssessmentForForm(mixed $saved): array
    {
        if (is_array($saved) && collect($saved)->contains(fn ($v) => filled($v))) {
            return self::normalizePhysicianAssessment($saved);
        }

        return self::defaultPhysicianAssessment();
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function normalizeClinicalAud(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultClinicalAud();
        }

        $metrics = ClinicalCompositeFields::resolveAudForForm(
            ['metrics' => $input['metrics'] ?? []],
            self::CLINICAL_AUD_KEYS,
            false
        )['metrics'];

        return ['metrics' => $metrics];
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function resolveClinicalAudForForm(mixed $saved, mixed $legacyClinicalAud = null): array
    {
        if (is_array($saved) && collect($saved['metrics'] ?? [])->contains(fn (array $row): bool => filled($row['value'] ?? null))) {
            return self::normalizeClinicalAud($saved);
        }

        if (is_array($legacyClinicalAud) && collect($legacyClinicalAud['metrics'] ?? [])->contains(fn (array $row): bool => filled($row['value'] ?? null))) {
            return self::normalizeClinicalAud($legacyClinicalAud);
        }

        return self::defaultClinicalAud();
    }

    public static function hasPhysicianAssessmentContent(mixed $value): bool
    {
        $data = self::normalizePhysicianAssessment($value);

        return filled($data['wound'])
            || filled($data['implant_bed'])
            || filled($data['facial_nerve'])
            || filled($data['post_op_xray']);
    }

    public static function hasClinicalAudContent(mixed $value): bool
    {
        return collect(self::normalizeClinicalAud($value)['metrics'] ?? [])
            ->contains(fn (array $row): bool => filled($row['value'] ?? null));
    }

    public static function hasNotesContent(mixed $value): bool
    {
        return filled(self::normalizeNotes($value));
    }

    public static function presentPhysicianAssessment(mixed $value): string
    {
        $data = self::normalizePhysicianAssessment($value);
        $service = app(ClinicalSelectOptionService::class);
        $lines = [];

        foreach ([
            'wound' => ['post_op_wound', 'workflow.post_op.fields.wound'],
            'implant_bed' => ['post_op_implant_bed', 'workflow.post_op.fields.implant_bed'],
            'facial_nerve' => ['post_op_facial_nerve', 'workflow.post_op.fields.facial_nerve'],
            'post_op_xray' => ['post_op_xray', 'workflow.post_op.fields.post_op_xray'],
        ] as $field => [$category, $labelKey]) {
            if (! filled($data[$field])) {
                continue;
            }

            $lines[] = __($labelKey).': '.$service->labelFor(
                $category,
                $data[$field],
                self::SELECT_CATEGORY_CONFIG[$category] ?? null
            );
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentClinicalAud(mixed $value): string
    {
        $lines = collect(self::normalizeClinicalAud($value)['metrics'] ?? [])
            ->filter(fn (array $row): bool => filled($row['value'] ?? null))
            ->map(fn (array $row): string => ($row['key'] ?? '').': '.($row['value'] ?? ''))
            ->all();

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentNotes(mixed $value): string
    {
        $text = self::normalizeNotes($value);

        return filled($text) ? $text : '—';
    }
}
