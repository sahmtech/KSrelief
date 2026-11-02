<?php

namespace App\Support;

use App\Models\PatientStage;
use App\Services\MedicalRecordService;

final class PostOperationFormMetaBuilder
{
    public function __construct(
        private readonly MedicalRecordService $recordService,
    ) {}

    /**
     * Web parity for stage `post_operation` (display name: Post op / switch on).
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $stageCode = 'post_operation';
        $fields = $this->recordService->getStageFields($stageCode);

        return [
            'stage_code' => $stageCode,
            'display_name' => PatientStage::displayNameForCode($stageCode),
            'phase' => $this->resolvedPhase(),
            'title' => [
                'workflow' => __('workflow.title'),
                'stage' => __('workflow.post_op.title'),
            ],
            'field_order' => array_keys($fields),
            'fields' => $fields,
            'cards' => $this->cardLayout(),
            'select_options' => [
                'physician_assessment' => PostOperationFieldSupport::physicianAssessmentSelectGroups(),
            ],
            'composites' => [
                'physician_assessment' => [
                    'type' => 'post_op_physician_assessment',
                    'fields' => [
                        ['key' => 'wound', 'column' => 'half', 'category' => 'post_op_wound', 'label_key' => 'workflow.post_op.fields.wound'],
                        ['key' => 'implant_bed', 'column' => 'half', 'category' => 'post_op_implant_bed', 'label_key' => 'workflow.post_op.fields.implant_bed'],
                        ['key' => 'facial_nerve', 'column' => 'half', 'category' => 'post_op_facial_nerve', 'label_key' => 'workflow.post_op.fields.facial_nerve'],
                        ['key' => 'post_op_xray', 'column' => 'half', 'category' => 'post_op_xray', 'label_key' => 'workflow.post_op.fields.post_op_xray'],
                    ],
                    'shape' => [
                        'wound' => 'string|null',
                        'implant_bed' => 'string|null',
                        'facial_nerve' => 'string|null',
                        'post_op_xray' => 'string|null',
                    ],
                ],
                'clinical_aud' => [
                    'type' => 'post_op_clinical_aud',
                    'shape' => ['metrics' => 'list<{key: string, value: string}>'],
                    'default_metric_keys' => PostOperationFieldSupport::CLINICAL_AUD_KEYS,
                    'allow_extra_metric_rows' => true,
                    'legacy_same_field' => true,
                ],
            ],
            'widgets' => $this->widgetMap(),
            'defaults_empty' => PostOperationFieldSupport::defaultFormPayload(),
            'counselling' => [
                'widget' => 'yes_no_checkbox',
                'stored_values' => ['yes', 'no'],
                'default' => 'no',
                'label_key' => 'workflow.post_op.fields.counselling',
            ],
            'post_op_notes' => [
                'widget' => 'textarea',
                'rows' => 5,
                'placeholder_key' => 'workflow.post_op.notes_placeholder',
            ],
            'templates' => [
                'user_defaults' => [
                    'get_path' => '/api/v1/patients/{patientId}/records/post-operation-defaults',
                    'response_key' => 'data',
                    'flag' => 'template_flags.has_post_operation_defaults',
                ],
                'save_defaults' => [
                    'supported' => false,
                    'note' => 'Web POST save_post_operation_defaults on record store only.',
                ],
            ],
            'clinical_select' => [
                'categories' => array_keys(PostOperationFieldSupport::SELECT_CATEGORY_CONFIG),
                'inline_add_supported_on_web' => true,
                'inline_add_api' => null,
            ],
            'activation_stage_note' => [
                'hidden_from_record_form' => true,
                'stage_code' => 'activation',
                'fields_in_config_not_on_web_form' => [
                    'coordinator',
                    'activation_date',
                    'switch_on',
                    'switch_on_note',
                    'activation_result',
                    'comments',
                ],
                'mobile_guidance' => 'Do not show activation fields unless product adds a separate stage UI; current web Post op / switch on record uses only the four keys in field_order.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvedPhase(): array
    {
        $phase = config('patient_clinical.phases.post_op', []);

        return [
            'code' => 'post_op',
            'label' => __($phase['label'] ?? 'workflow.phases.post_op'),
            'color' => $phase['color'] ?? '#3C78D8',
            'background' => $phase['background'] ?? '#9FC5E8',
        ];
    }

    /**
     * Mirrors card structure in `_post_operation_stage_fields.blade.php`.
     *
     * @return list<array{key: string, header_label_key: string, widget: string}>
     */
    private function cardLayout(): array
    {
        return [
            [
                'key' => 'physician_assessment',
                'header_label_key' => 'workflow.post_op.cards.physician_assessment',
                'widget' => 'post_op_physician_assessment',
            ],
            [
                'key' => 'clinical_aud',
                'header_label_key' => 'workflow.post_op.cards.clinical_aud',
                'widget' => 'post_op_clinical_aud',
            ],
            [
                'key' => 'counselling',
                'header_label_key' => null,
                'widget' => 'yes_no',
                'compact_card' => true,
            ],
            [
                'key' => 'post_op_notes',
                'header_label_key' => 'workflow.post_op.cards.notes',
                'widget' => 'post_op_notes',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function widgetMap(): array
    {
        return [
            'physician_assessment' => 'post_op_physician_assessment',
            'clinical_aud' => 'post_op_clinical_aud',
            'counselling' => 'yes_no',
            'post_op_notes' => 'post_op_notes',
        ];
    }
}
