<?php

namespace App\Support;

use App\Services\ClinicalSelectOptionService;

final class PreOperationFieldSupport
{
    public const HEARING_ASSESSMENT_KEYS = ['Diagnosis', 'HA usage', 'Deafness age', 'Hearing level'];

    /** @var array<string, string> */
    public const SELECT_CATEGORY_CONFIG = [
        'pre_op_general_condition' => 'medical_history_general_condition_options',
        'pre_op_pre_op_request' => 'medical_history_pre_op_request_options',
        'pre_op_clinical_decision' => 'pre_op_clinical_decision_options',
        'pre_op_audiology_status' => 'pre_op_audiology_status_options',
        'pre_op_audiology_decision' => 'pre_op_audiology_decision_options',
        'pre_op_speech_communication_mood' => 'pre_op_speech_communication_mood_options',
        'pre_op_speech_iq' => 'pre_op_speech_iq_options',
        'pre_op_speech_cognitive_function' => 'pre_op_speech_cognitive_function_options',
        'pre_op_speech_true_word' => 'pre_op_speech_true_word_options',
        'pre_op_speech_phrases' => 'pre_op_speech_phrases_options',
        'pre_op_speech_assessment' => 'pre_op_speech_assessment_options',
        'pre_op_speech_decision' => 'pre_op_speech_decision_options',
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
     * @return array<string, string>
     */
    public static function physicianAssessmentSelectGroups(): array
    {
        return [
            'general_condition' => self::selectOptions('pre_op_general_condition'),
            'pre_op_request' => self::selectOptions('pre_op_pre_op_request'),
            'clinical_decision' => self::selectOptions('pre_op_clinical_decision'),
        ];
    }

    /**
     * @return array{general_condition: ?string, pre_op_request: ?string, clinical_decision: ?string, notes: string}
     */
    public static function defaultPhysicianAssessment(): array
    {
        return [
            'general_condition' => null,
            'pre_op_request' => null,
            'clinical_decision' => null,
            'notes' => '',
        ];
    }

    /**
     * @return array{status: ?string, decision: ?string, metrics: list<array{key: string, value: string}>, audiology_link: string}
     */
    public static function defaultAudiologyDecision(): array
    {
        return [
            'status' => null,
            'decision' => null,
            'metrics' => ClinicalCompositeFields::defaultMetrics(self::HEARING_ASSESSMENT_KEYS),
            'audiology_link' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultSpeechAssessment(): array
    {
        return [
            'communication_mood' => null,
            'iq' => null,
            'cognitive_function' => null,
            'true_word' => null,
            'phrases' => null,
            'expectations_post_ci' => null,
            'assessment' => null,
            'speech_decision' => null,
            'notes' => '',
        ];
    }

    /**
     * @return array{general_condition: ?string, pre_op_request: ?string, clinical_decision: ?string, notes: string}
     */
    public static function normalizePhysicianAssessment(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultPhysicianAssessment();
        }

        return [
            'general_condition' => filled($input['general_condition'] ?? null) ? (string) $input['general_condition'] : null,
            'pre_op_request' => filled($input['pre_op_request'] ?? null) ? (string) $input['pre_op_request'] : null,
            'clinical_decision' => filled($input['clinical_decision'] ?? null) ? (string) $input['clinical_decision'] : null,
            'notes' => trim((string) ($input['notes'] ?? '')),
        ];
    }

    /**
     * @return array{general_condition: ?string, pre_op_request: ?string, clinical_decision: ?string, notes: string}
     */
    public static function resolvePhysicianAssessmentForForm(mixed $saved, mixed $legacyMedicalHistory = null): array
    {
        if (is_array($saved) && (
            filled($saved['general_condition'] ?? null)
            || filled($saved['pre_op_request'] ?? null)
            || filled($saved['clinical_decision'] ?? null)
            || filled($saved['notes'] ?? null)
        )) {
            return self::normalizePhysicianAssessment($saved);
        }

        if (is_array($legacyMedicalHistory)) {
            return self::normalizePhysicianAssessment([
                'general_condition' => $legacyMedicalHistory['general_condition'] ?? null,
                'pre_op_request' => $legacyMedicalHistory['pre_op_request'] ?? null,
                'clinical_decision' => null,
                'notes' => '',
            ]);
        }

        return self::defaultPhysicianAssessment();
    }

    /**
     * @return array{status: ?string, decision: ?string, metrics: list<array{key: string, value: string}>, audiology_link: string}
     */
    public static function normalizeAudiologyDecision(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultAudiologyDecision();
        }

        $metrics = ClinicalCompositeFields::resolveAudForForm(
            ['metrics' => $input['metrics'] ?? []],
            self::HEARING_ASSESSMENT_KEYS,
            false
        )['metrics'];

        return [
            'status' => self::normalizeAudiologyStatus($input['status'] ?? null),
            'decision' => filled($input['decision'] ?? null) ? (string) $input['decision'] : null,
            'metrics' => $metrics,
            'audiology_link' => trim((string) ($input['audiology_link'] ?? '')),
        ];
    }

    public static function normalizeAudiologyStatus(mixed $status): ?string
    {
        if (! filled($status)) {
            return null;
        }

        $value = (string) $status;

        return match ($value) {
            'required_more', 'assessment' => 'required_more_assessment',
            default => $value,
        };
    }

    /**
     * @return array{status: ?string, decision: ?string, metrics: list<array{key: string, value: string}>, audiology_link: string}
     */
    public static function resolveAudiologyDecisionForForm(
        mixed $saved,
        mixed $legacyClinicalAud = null,
        mixed $legacyAudiologyLink = null,
        mixed $legacyDeafnessAge = null,
    ): array {
        if (is_array($saved) && (
            filled($saved['status'] ?? null)
            || filled($saved['decision'] ?? null)
            || filled($saved['audiology_link'] ?? null)
            || collect($saved['metrics'] ?? [])->contains(fn (array $row): bool => filled($row['value'] ?? null))
        )) {
            return self::normalizeAudiologyDecision($saved);
        }

        if (is_array($legacyClinicalAud)) {
            $data = self::defaultAudiologyDecision();
            $resolved = ClinicalCompositeFields::resolveAudForForm($legacyClinicalAud, self::HEARING_ASSESSMENT_KEYS, false);
            $data['status'] = self::normalizeAudiologyStatus($legacyClinicalAud['status'] ?? null);
            $data['metrics'] = $resolved['metrics'];

            if (filled($legacyDeafnessAge)) {
                foreach ($data['metrics'] as &$row) {
                    if (($row['key'] ?? '') === 'Deafness age') {
                        $row['value'] = (string) $legacyDeafnessAge;
                    }
                }
                unset($row);
            }

            $data['audiology_link'] = trim((string) ($legacyAudiologyLink ?? ''));

            return $data;
        }

        $data = self::defaultAudiologyDecision();
        $data['audiology_link'] = trim((string) ($legacyAudiologyLink ?? ''));

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function normalizeSpeechAssessment(mixed $input): array
    {
        if (! is_array($input)) {
            return self::defaultSpeechAssessment();
        }

        $expectations = $input['expectations_post_ci'] ?? null;

        return [
            'communication_mood' => filled($input['communication_mood'] ?? null) ? (string) $input['communication_mood'] : null,
            'iq' => filled($input['iq'] ?? null) ? (string) $input['iq'] : null,
            'cognitive_function' => filled($input['cognitive_function'] ?? null) ? (string) $input['cognitive_function'] : null,
            'true_word' => filled($input['true_word'] ?? null) ? (string) $input['true_word'] : null,
            'phrases' => filled($input['phrases'] ?? null) ? (string) $input['phrases'] : null,
            'expectations_post_ci' => filled($expectations) ? (string) $expectations : null,
            'assessment' => filled($input['assessment'] ?? null) ? (string) $input['assessment'] : null,
            'speech_decision' => filled($input['speech_decision'] ?? null) ? (string) $input['speech_decision'] : null,
            'notes' => trim((string) ($input['notes'] ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolveSpeechAssessmentForForm(
        mixed $saved,
        mixed $legacyClinicalSpeech = null,
        mixed $legacyExpectations = null,
    ): array {
        if (is_array($saved) && collect($saved)->except('notes')->contains(fn ($v) => filled($v)) || filled($saved['notes'] ?? null)) {
            return array_merge(self::defaultSpeechAssessment(), self::normalizeSpeechAssessment($saved));
        }

        $data = self::defaultSpeechAssessment();

        if (is_array($legacyClinicalSpeech)) {
            $data['notes'] = trim((string) ($legacyClinicalSpeech['notes'] ?? ''));
            $legacyAssessment = (string) ($legacyClinicalSpeech['assessment'] ?? '');
            $data['assessment'] = match ($legacyAssessment) {
                'updated' => 'complete',
                'need_assessment' => 'required_more_assessment',
                'poor_outcome' => 'not_done_yet',
                default => filled($legacyAssessment) ? $legacyAssessment : null,
            };
        }

        if (is_array($legacyExpectations)) {
            $selected = $legacyExpectations['selected'] ?? [];
            if (is_array($selected) && $selected !== []) {
                $data['expectations_post_ci'] = (string) ($selected[0] ?? '');
            }
        } elseif (filled($legacyExpectations)) {
            $data['expectations_post_ci'] = (string) $legacyExpectations;
        }

        return $data;
    }

    public static function hasPhysicianAssessmentContent(mixed $value): bool
    {
        $data = self::normalizePhysicianAssessment($value);

        return filled($data['general_condition'])
            || filled($data['pre_op_request'])
            || filled($data['clinical_decision'])
            || filled($data['notes']);
    }

    public static function hasAudiologyDecisionContent(mixed $value): bool
    {
        $data = self::normalizeAudiologyDecision($value);

        return filled($data['status'])
            || filled($data['decision'])
            || filled($data['audiology_link'])
            || collect($data['metrics'])->contains(fn (array $row): bool => filled($row['value'] ?? null));
    }

    public static function hasSpeechAssessmentContent(mixed $value): bool
    {
        $data = self::normalizeSpeechAssessment($value);

        return collect($data)->contains(fn ($v) => filled($v));
    }

    public static function presentPhysicianAssessment(mixed $value): string
    {
        $data = self::normalizePhysicianAssessment($value);
        $service = app(ClinicalSelectOptionService::class);
        $lines = [];

        foreach ([
            'general_condition' => ['pre_op_general_condition', 'workflow.pre_op.fields.general_condition'],
            'pre_op_request' => ['pre_op_pre_op_request', 'workflow.pre_op.fields.pre_op_request'],
            'clinical_decision' => ['pre_op_clinical_decision', 'workflow.pre_op.fields.clinical_decision'],
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

        if (filled($data['notes'])) {
            $lines[] = __('workflow.pre_op.fields.physician_notes').': '.$data['notes'];
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentAudiologyDecision(mixed $value): string
    {
        $data = self::normalizeAudiologyDecision($value);
        $service = app(ClinicalSelectOptionService::class);
        $lines = [];

        if (filled($data['status'])) {
            $lines[] = __('workflow.pre_op.fields.audiology_status').': '.$service->labelFor(
                'pre_op_audiology_status',
                $data['status'],
                self::SELECT_CATEGORY_CONFIG['pre_op_audiology_status']
            );
        }

        if (filled($data['decision'])) {
            $lines[] = __('workflow.pre_op.fields.audiology_decision').': '.$service->labelFor(
                'pre_op_audiology_decision',
                $data['decision'],
                self::SELECT_CATEGORY_CONFIG['pre_op_audiology_decision']
            );
        }

        $metricLines = collect($data['metrics'])
            ->filter(fn (array $row): bool => filled($row['value'] ?? null))
            ->map(fn (array $row): string => ($row['key'] ?? '').': '.($row['value'] ?? ''))
            ->all();

        if ($metricLines !== []) {
            $lines[] = __('workflow.pre_op.fields.hearing_assessment').': '.implode(', ', $metricLines);
        }

        if (filled($data['audiology_link'])) {
            $lines[] = __('workflow.fields.audiology_link').': '.$data['audiology_link'];
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }

    public static function presentSpeechAssessment(mixed $value, array $expectationOptions = []): string
    {
        $data = self::normalizeSpeechAssessment($value);
        $service = app(ClinicalSelectOptionService::class);
        $fieldMap = [
            'communication_mood' => ['pre_op_speech_communication_mood', 'workflow.pre_op.fields.communication_mood'],
            'iq' => ['pre_op_speech_iq', 'workflow.pre_op.fields.iq'],
            'cognitive_function' => ['pre_op_speech_cognitive_function', 'workflow.pre_op.fields.cognitive_function'],
            'true_word' => ['pre_op_speech_true_word', 'workflow.pre_op.fields.true_word'],
            'phrases' => ['pre_op_speech_phrases', 'workflow.pre_op.fields.phrases'],
            'assessment' => ['pre_op_speech_assessment', 'workflow.pre_op.fields.assessment'],
            'speech_decision' => ['pre_op_speech_decision', 'workflow.pre_op.fields.speech_decision'],
        ];

        $lines = [];

        foreach ($fieldMap as $field => [$category, $labelKey]) {
            if (! filled($data[$field])) {
                continue;
            }
            $lines[] = __($labelKey).': '.$service->labelFor(
                $category,
                $data[$field],
                self::SELECT_CATEGORY_CONFIG[$category] ?? null
            );
        }

        if (filled($data['expectations_post_ci'])) {
            $label = $expectationOptions[$data['expectations_post_ci']] ?? $data['expectations_post_ci'];
            $lines[] = __('workflow.fields.expectations_post_ci').': '.$label;
        }

        if (filled($data['notes'])) {
            $lines[] = __('workflow.pre_op.fields.speech_notes').': '.$data['notes'];
        }

        return $lines === [] ? '—' : implode("\n", $lines);
    }
}
