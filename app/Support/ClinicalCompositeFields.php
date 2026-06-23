<?php

namespace App\Support;

final class ClinicalCompositeFields
{
    /**
     * @return list<string>
     */
    public static function metricsKeysForProfile(?string $profile): array
    {
        if ($profile === null || $profile === '') {
            return config('patient_clinical.clinical_aud_profiles.screening', ['Hearing level']);
        }

        return config("patient_clinical.clinical_aud_profiles.{$profile}", []);
    }

    /**
     * @param  array<string, mixed>  $fieldDefinition
     * @return list<string>
     */
    public static function metricsKeysFromDefinition(array $fieldDefinition): array
    {
        if (isset($fieldDefinition['metrics_keys']) && is_array($fieldDefinition['metrics_keys'])) {
            return $fieldDefinition['metrics_keys'];
        }

        return self::metricsKeysForProfile($fieldDefinition['metrics_profile'] ?? 'screening');
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    public static function defaultMetrics(array $keys): array
    {
        return collect($keys)
            ->map(fn (string $key): array => ['key' => $key, 'value' => ''])
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    public static function defaultAudMetrics(?string $profile = 'screening'): array
    {
        return self::defaultMetrics(self::metricsKeysForProfile($profile));
    }

    /**
     * @param  list<string>  $defaultKeys
     * @return array{metrics: list<array{key: string, value: string}>, status: ?string}
     */
    public static function normalizeAud(mixed $input, array $defaultKeys, bool $withStatus = true): array
    {
        if (is_string($input) && filled(trim($input))) {
            return [
                'metrics' => [['key' => 'Notes', 'value' => trim($input)]],
                'status' => null,
            ];
        }

        if (! is_array($input)) {
            return [
                'metrics' => self::defaultMetrics($defaultKeys),
                'status' => null,
            ];
        }

        $metrics = self::extractMetrics($input['metrics'] ?? []);

        $status = $withStatus && filled($input['status'] ?? null) ? (string) $input['status'] : null;

        return [
            'metrics' => $metrics,
            'status' => $status,
        ];
    }

    /**
     * @param  list<string>  $defaultKeys
     * @return array{metrics: list<array{key: string, value: string}>, status: string}
     */
    public static function resolveAudForForm(mixed $saved, array $defaultKeys, bool $withStatus = true): array
    {
        $normalized = self::normalizeAud($saved, $defaultKeys, $withStatus);
        $savedByKey = collect($normalized['metrics'])->keyBy('key');
        $metrics = [];

        foreach ($defaultKeys as $defaultKey) {
            $metrics[] = [
                'key' => $defaultKey,
                'value' => (string) ($savedByKey->pull($defaultKey)['value'] ?? ''),
            ];
        }

        foreach ($savedByKey as $row) {
            $metrics[] = [
                'key' => (string) ($row['key'] ?? ''),
                'value' => (string) ($row['value'] ?? ''),
            ];
        }

        if ($metrics === []) {
            $metrics = self::defaultMetrics($defaultKeys);
        }

        return [
            'metrics' => $metrics,
            'status' => (string) ($normalized['status'] ?? ''),
        ];
    }

    /**
     * @return array{notes: string, assessment: string}
     */
    public static function resolveSpeechForForm(mixed $saved): array
    {
        $normalized = self::normalizeSpeech($saved);

        return [
            'notes' => (string) ($normalized['notes'] ?? ''),
            'assessment' => (string) ($normalized['assessment'] ?? ''),
        ];
    }

    /**
     * @return array{notes: string, assessment: ?string}
     */
    public static function normalizeSpeech(mixed $input): array
    {
        if (is_string($input)) {
            return [
                'notes' => trim($input),
                'assessment' => null,
            ];
        }

        if (! is_array($input)) {
            return ['notes' => '', 'assessment' => null];
        }

        if (isset($input['metrics'])) {
            return ['notes' => '', 'assessment' => null];
        }

        $assessment = filled($input['assessment'] ?? null) ? (string) $input['assessment'] : null;

        return [
            'notes' => trim((string) ($input['notes'] ?? '')),
            'assessment' => $assessment,
        ];
    }

    /**
     * @param  list<string>  $defaultKeys
     * @return array{metrics: list<array{key: string, value: string}>, assessment: ?string}
     */
    public static function normalizeSpeechFollowup(mixed $input, array $defaultKeys): array
    {
        if (is_string($input) && filled(trim($input))) {
            return [
                'metrics' => [['key' => 'Notes', 'value' => trim($input)]],
                'assessment' => null,
            ];
        }

        if (! is_array($input)) {
            return [
                'metrics' => self::defaultMetrics($defaultKeys),
                'assessment' => null,
            ];
        }

        $assessment = filled($input['assessment'] ?? null) ? (string) $input['assessment'] : null;

        return [
            'metrics' => self::extractMetrics($input['metrics'] ?? []),
            'assessment' => $assessment,
        ];
    }

    /**
     * @param  list<string>  $defaultKeys
     * @return array{metrics: list<array{key: string, value: string}>, assessment: string}
     */
    public static function resolveSpeechFollowupForForm(mixed $saved, array $defaultKeys): array
    {
        $normalized = self::normalizeSpeechFollowup($saved, $defaultKeys);
        $savedByKey = collect($normalized['metrics'])->keyBy('key');
        $metrics = [];

        foreach ($defaultKeys as $defaultKey) {
            $metrics[] = [
                'key' => $defaultKey,
                'value' => (string) ($savedByKey->pull($defaultKey)['value'] ?? ''),
            ];
        }

        foreach ($savedByKey as $row) {
            $metrics[] = [
                'key' => (string) ($row['key'] ?? ''),
                'value' => (string) ($row['value'] ?? ''),
            ];
        }

        if ($metrics === []) {
            $metrics = self::defaultMetrics($defaultKeys);
        }

        return [
            'metrics' => $metrics,
            'assessment' => (string) ($normalized['assessment'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $fieldDefinition
     */
    public static function hasContent(string $fieldKey, mixed $value, array $fieldDefinition = []): bool
    {
        $type = $fieldDefinition['type'] ?? '';

        if ($type === 'clinical_aud' || $fieldKey === 'clinical_aud') {
            $keys = self::metricsKeysFromDefinition($fieldDefinition);
            $withStatus = (bool) ($fieldDefinition['with_status'] ?? true);
            $data = self::normalizeAud($value, $keys, $withStatus);

            if ($withStatus && filled($data['status'])) {
                return true;
            }

            return collect($data['metrics'])->contains(fn (array $row): bool => filled($row['value'] ?? null));
        }

        if ($type === 'clinical_speech_followup') {
            $keys = config('patient_clinical.clinical_speech_follow_up_keys', ['Cap', 'SIR']);
            $data = self::normalizeSpeechFollowup($value, $keys);

            if (filled($data['assessment'])) {
                return true;
            }

            return collect($data['metrics'])->contains(fn (array $row): bool => filled($row['value'] ?? null));
        }

        if ($type === 'clinical_speech' || $fieldKey === 'clinical_speech') {
            $data = self::normalizeSpeech($value);

            return filled($data['notes']) || filled($data['assessment']);
        }

        if ($type === 'expandable_checklist') {
            return ScreeningFieldSupport::hasExpandableChecklistContent($value, $fieldDefinition);
        }

        if ($type === 'medical_history_screening') {
            return ScreeningFieldSupport::hasMedicalHistoryScreeningContent($value);
        }

        if ($type === 'imaging_findings') {
            return ScreeningFieldSupport::hasImagingFindingsContent($value);
        }

        if ($type === 'select') {
            return filled($value);
        }

        return filled($value);
    }

    /**
     * @param  array<string, mixed>  $fieldDefinition
     */
    public static function present(string $fieldKey, mixed $value, array $fieldDefinition = []): string
    {
        $type = $fieldDefinition['type'] ?? $fieldKey;

        return match ($type) {
            'clinical_aud' => self::presentAud($value, $fieldDefinition),
            'clinical_speech_followup' => self::presentSpeechFollowup($value),
            'clinical_speech' => self::presentSpeech($value),
            'expandable_checklist' => ScreeningFieldSupport::presentExpandableChecklist($value, $fieldDefinition),
            'medical_history_screening' => ScreeningFieldSupport::presentMedicalHistoryScreening($value),
            'imaging_findings' => ScreeningFieldSupport::presentImagingFindings($value),
            'select' => ScreeningFieldSupport::selectOptionLabel($value, $fieldDefinition),
            default => (string) $value,
        };
    }

    /**
     * @param  array<string, mixed>  $fieldDefinition
     */
    public static function presentAud(mixed $value, array $fieldDefinition = []): string
    {
        $keys = self::metricsKeysFromDefinition($fieldDefinition);
        $withStatus = (bool) ($fieldDefinition['with_status'] ?? true);
        $data = self::normalizeAud($value, $keys, $withStatus);
        $lines = [];

        foreach ($data['metrics'] as $row) {
            if (filled($row['value'] ?? null)) {
                $lines[] = ($row['key'] ?? '').': '.($row['value'] ?? '');
            }
        }

        if ($withStatus && filled($data['status'])) {
            $lines[] = __('workflow.fields.clinical_aud_status').': '.self::audStatusLabel((string) $data['status']);
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentSpeech(mixed $value): string
    {
        $data = self::normalizeSpeech($value);
        $lines = [];

        if (filled($data['notes'])) {
            $lines[] = $data['notes'];
        }

        if (filled($data['assessment'])) {
            $lines[] = __('workflow.fields.clinical_speech_assessment').': '.self::speechAssessmentLabel((string) $data['assessment']);
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentSpeechFollowup(mixed $value): string
    {
        $keys = config('patient_clinical.clinical_speech_follow_up_keys', ['Cap', 'SIR']);
        $data = self::normalizeSpeechFollowup($value, $keys);
        $lines = [];

        foreach ($data['metrics'] as $row) {
            if (filled($row['value'] ?? null)) {
                $lines[] = ($row['key'] ?? '').': '.($row['value'] ?? '');
            }
        }

        if (filled($data['assessment'])) {
            $lines[] = __('workflow.fields.clinical_speech_assessment').': '.self::speechAssessmentLabel((string) $data['assessment']);
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function audStatusLabel(string $code): string
    {
        $options = config('patient_clinical.clinical_aud_status_options', []);

        return __($options[$code] ?? $code);
    }

    public static function speechAssessmentLabel(string $code): string
    {
        foreach (['clinical_speech_screening_assessment_options', 'clinical_speech_assessment_options'] as $configKey) {
            $options = config("patient_clinical.{$configKey}", []);
            if (isset($options[$code])) {
                return __($options[$code]);
            }
        }

        return $code;
    }

    /**
     * @return array<string, string>
     */
    public static function audStatusOptions(): array
    {
        return collect(config('patient_clinical.clinical_aud_status_options', []))
            ->mapWithKeys(fn (string $label, string $code): array => [$code => __($label)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function speechAssessmentOptions(): array
    {
        return collect(config('patient_clinical.clinical_speech_assessment_options', []))
            ->mapWithKeys(fn (string $label, string $code): array => [$code => __($label)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function speechScreeningAssessmentOptions(): array
    {
        return collect(config('patient_clinical.clinical_speech_screening_assessment_options', []))
            ->mapWithKeys(fn (string $label, string $code): array => [$code => __($label)])
            ->all();
    }

    /**
     * @param  list<mixed>  $rows
     * @return list<array{key: string, value: string}>
     */
    private static function extractMetrics(array $rows): array
    {
        $metrics = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = trim((string) ($row['key'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));

            if ($key === '' && $value === '') {
                continue;
            }

            if ($key === '') {
                continue;
            }

            $metrics[] = ['key' => $key, 'value' => $value];
        }

        return $metrics;
    }
}
