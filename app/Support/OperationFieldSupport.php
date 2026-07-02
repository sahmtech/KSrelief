<?php

namespace App\Support;

use App\Services\ClinicalSelectOptionService;

final class OperationFieldSupport
{
    /** @var list<string> */
    public const AUDIO_TEST_KEYS = ['Impedance', 'E cap'];

    /** @var array<string, string> */
    public const SELECT_CATEGORY_CONFIG = [
        'operation_intra_op_findings' => 'operation_intra_op_findings_options',
        'operation_insertion_depth' => 'operation_insertion_depth_options',
    ];

    /**
     * @return array<string, string>
     */
    public static function insertionDepthOptions(): array
    {
        return app(ClinicalSelectOptionService::class)->optionsForCategory(
            'operation_insertion_depth',
            self::SELECT_CATEGORY_CONFIG['operation_insertion_depth']
        );
    }

    /**
     * @return array<string, string>
     */
    public static function intraOpFindingsOptions(): array
    {
        return app(ClinicalSelectOptionService::class)->optionsForCategory(
            'operation_intra_op_findings',
            self::SELECT_CATEGORY_CONFIG['operation_intra_op_findings']
        );
    }

    /**
     * @return array{selection: ?string, note: string}
     */
    public static function defaultInsertionDepth(): array
    {
        return [
            'selection' => null,
            'note' => '',
        ];
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function defaultAudioTest(): array
    {
        return ['metrics' => ClinicalCompositeFields::defaultMetrics(self::AUDIO_TEST_KEYS)];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultTemplatePayload(): array
    {
        return [
            'surgeon' => null,
            'implant_company_id' => null,
            'electrode_type_id' => null,
            'insertion_approach_id' => null,
            'insertion_depth' => self::defaultInsertionDepth(),
            'time_in_surgery' => '',
            'time_out_surgery' => '',
            'audio_test' => self::defaultAudioTest(),
            'intra_op_findings' => '',
            'operation_notes' => '',
        ];
    }

    /**
     * @return array{selection: ?string, note: string}
     */
    public static function normalizeInsertionDepth(mixed $input): array
    {
        if (is_array($input) && array_key_exists('selection', $input)) {
            $selection = filled($input['selection'] ?? null) ? (string) $input['selection'] : null;
            $note = trim((string) ($input['note'] ?? ''));

            if ($selection !== 'partial_insertion') {
                $note = '';
            }

            return [
                'selection' => $selection,
                'note' => $note,
            ];
        }

        if (is_array($input)) {
            foreach ($input as $code => $row) {
                if ($code === 'selection' || $code === 'note') {
                    continue;
                }
                $note = trim((string) (is_array($row) ? ($row['note'] ?? '') : $row));
                if (filled($note)) {
                    return [
                        'selection' => (string) $code,
                        'note' => $note,
                    ];
                }
            }
        }

        return self::defaultInsertionDepth();
    }

    /**
     * @return array{selection: ?string, note: string}
     */
    public static function resolveInsertionDepthForForm(mixed $saved, mixed $legacyNote = null): array
    {
        $data = self::normalizeInsertionDepth($saved);

        if (blank($data['selection']) && blank($data['note']) && is_string($legacyNote) && filled(trim($legacyNote))) {
            return [
                'selection' => 'full_insertion',
                'note' => trim($legacyNote),
            ];
        }

        return $data;
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function normalizeAudioTest(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultAudioTest();
        }

        $metrics = ClinicalCompositeFields::resolveAudForForm(
            ['metrics' => $input['metrics'] ?? []],
            self::AUDIO_TEST_KEYS,
            false
        )['metrics'];

        return ['metrics' => $metrics];
    }

    /**
     * @return array{metrics: list<array{key: string, value: string}>}
     */
    public static function resolveAudioTestForForm(mixed $saved, mixed $legacyImpedance = null): array
    {
        if (is_array($saved) && collect($saved['metrics'] ?? [])->contains(fn (array $row): bool => filled($row['value'] ?? null))) {
            return self::normalizeAudioTest($saved);
        }

        if (is_string($legacyImpedance) && filled(trim($legacyImpedance))) {
            $data = self::defaultAudioTest();
            foreach ($data['metrics'] as &$row) {
                if (($row['key'] ?? '') === 'Impedance') {
                    $row['value'] = trim($legacyImpedance);
                }
            }
            unset($row);

            return $data;
        }

        return self::defaultAudioTest();
    }

    public static function hasInsertionDepthContent(mixed $value): bool
    {
        $data = self::normalizeInsertionDepth($value);

        return filled($data['selection']) || filled($data['note']);
    }

    public static function hasAudioTestContent(mixed $value): bool
    {
        return collect(self::normalizeAudioTest($value)['metrics'] ?? [])
            ->contains(fn (array $row): bool => filled($row['value'] ?? null));
    }

    public static function presentInsertionDepth(mixed $value): string
    {
        $data = self::normalizeInsertionDepth($value);

        if (! filled($data['selection'])) {
            return '—';
        }

        $label = self::insertionDepthOptions()[$data['selection']] ?? $data['selection'];

        if ($data['selection'] === 'partial_insertion' && filled($data['note'])) {
            return "{$label}: {$data['note']}";
        }

        return $label;
    }

    public static function presentAudioTest(mixed $value): string
    {
        $lines = collect(self::normalizeAudioTest($value)['metrics'] ?? [])
            ->filter(fn (array $row): bool => filled($row['value'] ?? null))
            ->map(fn (array $row): string => ($row['key'] ?? '').': '.($row['value'] ?? ''))
            ->all();

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentIntraOpFindings(mixed $value): string
    {
        if (! filled($value)) {
            return '—';
        }

        $service = app(ClinicalSelectOptionService::class);
        $label = $service->labelFor(
            'operation_intra_op_findings',
            (string) $value,
            self::SELECT_CATEGORY_CONFIG['operation_intra_op_findings']
        );

        return $label ?? (string) $value;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, string>
     */
    public static function applyLabelsForPayload(array $payload): array
    {
        $insertionDepth = self::normalizeInsertionDepth($payload['insertion_depth'] ?? null);
        $insertionLabel = filled($insertionDepth['selection'])
            ? (self::insertionDepthOptions()[$insertionDepth['selection']] ?? $insertionDepth['selection'])
            : null;

        $intraOpLabel = filled($payload['intra_op_findings'] ?? null)
            ? self::presentIntraOpFindings($payload['intra_op_findings'])
            : null;

        return array_filter([
            'insertion_depth' => $insertionLabel,
            'intra_op_findings' => $intraOpLabel !== '—' ? $intraOpLabel : null,
        ], fn (?string $value): bool => filled($value));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function payloadWithApplyLabels(array $payload): array
    {
        $labels = self::applyLabelsForPayload($payload);

        if ($labels === []) {
            return $payload;
        }

        return array_merge($payload, ['_apply_labels' => $labels]);
    }
}
