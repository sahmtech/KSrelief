<?php

namespace App\Support;

use App\Models\PatientStage;
use App\Services\MedicalRecordService;

final class FollowUpFormMetaBuilder
{
    public function __construct(
        private readonly MedicalRecordService $recordService,
    ) {}

    /**
     * Web parity for stage `follow_up`.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $stageCode = 'follow_up';
        $fields = $this->recordService->getStageFields($stageCode);

        return [
            'stage_code' => $stageCode,
            'display_name' => PatientStage::displayNameForCode($stageCode),
            'phase' => $this->resolvedPhase(),
            'title' => [
                'workflow' => __('workflow.title'),
                'stage' => PatientStage::displayNameForCode($stageCode),
            ],
            'field_order' => array_keys($fields),
            'fields' => $fields,
            'cards' => $this->cardLayout(),
            'select_options' => [
                'clinical_assessment' => FollowUpFieldSupport::clinicalAssessmentSelectGroups(),
                'speech_assessment' => FollowUpFieldSupport::speechAssessmentOptionGroups(),
            ],
            'composites' => [
                'clinical_assessment' => [
                    'type' => 'follow_up_clinical_assessment',
                    'fields' => [
                        ['key' => 'wound', 'column' => 'half', 'label_key' => 'workflow.follow_up.tables.wound'],
                        ['key' => 'implant_bed', 'column' => 'half', 'label_key' => 'workflow.follow_up.tables.implant_bed'],
                    ],
                    'shape' => [
                        'wound' => 'string|null',
                        'implant_bed' => 'string|null',
                    ],
                    'legacy_metrics_table_migration' => true,
                ],
                'audiology_assessment' => [
                    'type' => 'follow_up_audiology_assessment',
                    'hearing_assessment' => $this->hearingAssessmentMeta(),
                    'metrics' => [
                        'default_metric_keys' => FollowUpFieldSupport::AUDIOLOGY_DEFAULT_KEYS,
                        'allow_extra_metric_rows' => true,
                        'table_title_key' => 'workflow.hearing_assessment.metrics_table',
                        'removed_keys_on_save' => ['Hearing level', 'SRT', 'SDS'],
                    ],
                    'shape' => [
                        'hearing_types' => 'list<string>',
                        'assessments' => 'object keyed by hearing type',
                        'metrics' => 'list<{key: string, value: string}>',
                    ],
                ],
                'speech_assessment' => [
                    'type' => 'follow_up_speech_assessment',
                    'select_fields' => [
                        ['key' => 'communication_mood', 'column' => 'third', 'label_key' => 'workflow.follow_up.fields.communication_mood'],
                        ['key' => 'true_word', 'column' => 'third', 'label_key' => 'workflow.follow_up.fields.true_word'],
                        ['key' => 'phrases', 'column' => 'third', 'label_key' => 'workflow.follow_up.fields.phrases'],
                    ],
                    'text_fields' => [
                        ['key' => 'cap', 'column' => 'half', 'label_key' => 'workflow.follow_up.fields.cap'],
                        ['key' => 'sir', 'column' => 'half', 'label_key' => 'workflow.follow_up.fields.sir'],
                    ],
                    'shape' => [
                        'communication_mood' => 'string|null',
                        'true_word' => 'string|null',
                        'phrases' => 'string|null',
                        'cap' => 'string',
                        'sir' => 'string',
                    ],
                ],
            ],
            'widgets' => $this->widgetMap(),
            'defaults_empty' => FollowUpFieldSupport::defaultFormPayload(),
            'follow_up_notes' => [
                'widget' => 'textarea',
                'rows' => 5,
                'placeholder_key' => 'workflow.follow_up.notes_placeholder',
            ],
            'templates' => [
                'user_defaults' => [
                    'get_path' => '/api/v1/patients/{patientId}/records/follow-up-defaults',
                    'response_key' => 'data',
                    'flag' => 'template_flags.has_follow_up_defaults',
                ],
                'save_defaults' => [
                    'supported' => false,
                    'note' => 'Web POST save_follow_up_defaults on record store only.',
                ],
            ],
            'hearing_assessment_reference' => [
                'same_engine_as_pre_op' => true,
                'config' => 'config/hearing_assessment.php',
                'support_class' => 'App\Support\HearingAssessmentSupport',
                'pre_op_spec_section' => 'mobile-api-medical-records-pre-operation-full-spec.md § audiology hearing_types',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvedPhase(): array
    {
        $phase = config('patient_clinical.phases.follow_up', []);

        return [
            'code' => 'follow_up',
            'label' => __($phase['label'] ?? 'workflow.phases.follow_up'),
            'color' => $phase['color'] ?? '#8B5CF6',
            'background' => $phase['background'] ?? '#EDE9FE',
        ];
    }

    /**
     * Mirrors `_follow_up_stage_fields.blade.php`.
     *
     * @return list<array{key: string, header_label_key: string, widget: string}>
     */
    private function cardLayout(): array
    {
        return [
            [
                'key' => 'clinical_assessment',
                'header_label_key' => 'workflow.follow_up.cards.clinical_assessment',
                'widget' => 'follow_up_clinical_assessment',
            ],
            [
                'key' => 'audiology_assessment',
                'header_label_key' => 'workflow.follow_up.cards.audiology_assessment',
                'widget' => 'follow_up_audiology_assessment',
            ],
            [
                'key' => 'speech_assessment',
                'header_label_key' => 'workflow.follow_up.cards.speech_assessment',
                'widget' => 'follow_up_speech_assessment',
            ],
            [
                'key' => 'follow_up_notes',
                'header_label_key' => 'workflow.follow_up.cards.notes',
                'widget' => 'follow_up_notes',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function widgetMap(): array
    {
        return [
            'clinical_assessment' => 'follow_up_clinical_assessment',
            'audiology_assessment' => 'follow_up_audiology_assessment',
            'speech_assessment' => 'follow_up_speech_assessment',
            'follow_up_notes' => 'follow_up_notes',
        ];
    }

    /**
     * Same catalog as pre-operation audiology hearing workflow (shared component).
     *
     * @return array<string, mixed>
     */
    private function hearingAssessmentMeta(): array
    {
        $types = [];

        foreach (HearingAssessmentSupport::types() as $typeKey => $meta) {
            $types[$typeKey] = [
                'label' => __($meta['label_key']),
                'layout' => $meta['layout'] ?? '',
                'extras' => $meta['extras'] ?? [],
            ];

            if (($meta['layout'] ?? '') === 'ear_options' && isset($meta['options_key'])) {
                $optionsKey = $meta['options_key'];
                $types[$typeKey]['ear_options'] = collect(config("hearing_assessment.ear_options.{$optionsKey}", []))
                    ->mapWithKeys(fn (string $labelKey, string $value): array => [$value => __($labelKey)])
                    ->all();
            }
        }

        return [
            'frequencies' => config('hearing_assessment.frequencies', []),
            'types' => $types,
        ];
    }
}
