<?php

namespace App\Support;

use App\Services\LookupService;

final class PreOperationFormMetaBuilder
{
    public function __construct(
        private readonly LookupService $lookupService,
    ) {}

    /**
     * Full UI catalog for pre-operation medical record form (web parity).
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        return [
            'stage_code' => 'pre_operation',
            'phase' => config('patient_clinical.phases.pre_op'),
            'field_keys' => [
                'physician_assessment',
                'imaging_findings',
                'audiology_decision',
                'speech_assessment',
            ],
            'defaults_empty' => PreOperationFieldSupport::defaultFormPayload(),
            'select_options' => [
                'physician_assessment' => PreOperationFieldSupport::physicianAssessmentSelectGroups(),
                'audiology_decision' => [
                    'status' => PreOperationFieldSupport::selectOptions('pre_op_audiology_status'),
                    'decision' => PreOperationFieldSupport::selectOptions('pre_op_audiology_decision'),
                ],
                'speech_assessment' => $this->speechSelectOptions(),
            ],
            'expectation_post_ci_options' => $this->lookupService->getExpectationPostCiOptions()
                ->mapWithKeys(fn ($option): array => [(string) $option->id => $option->name])
                ->all(),
            'audiology_metrics_default_keys' => PreOperationFieldSupport::HEARING_ASSESSMENT_KEYS,
            'hearing_assessment' => $this->hearingAssessmentMeta(),
            'imaging_findings' => [
                'modalities' => ImagingFindingsTreeSupport::modalities(),
                'ears' => ImagingFindingsTreeSupport::ears(),
                'trees' => [
                    'ct' => $this->translateImagingTree(config('imaging_findings_tree.ct', [])),
                    'mri' => $this->translateImagingTree(config('imaging_findings_tree.mri', [])),
                ],
                'ct_options_legacy' => $this->lookupService->getCtFindingOptions()
                    ->mapWithKeys(fn ($option): array => [(string) $option->id => $option->name])
                    ->all(),
                'mri_options_legacy' => $this->lookupService->getMriFindingOptions()
                    ->mapWithKeys(fn ($option): array => [(string) $option->id => $option->name])
                    ->all(),
            ],
            'clinical_select_categories' => array_keys(PreOperationFieldSupport::SELECT_CATEGORY_CONFIG),
            'template' => [
                'defaults_get_path' => '/api/v1/patients/{patientId}/records/pre-operation-defaults',
                'save_defaults_web_only' => true,
            ],
            'legacy_prefill_sources' => [
                'patient_screening_keys' => [
                    'medical_history' => 'physician_assessment (partial)',
                    'imaging_findings' => 'imaging_findings',
                    'clinical_aud' => 'audiology_decision (partial)',
                    'audiology_link' => 'audiology_decision.audiology_link',
                    'deafness_age' => 'audiology_decision.metrics Deafness age',
                    'clinical_speech' => 'speech_assessment (partial)',
                    'expectations_post_ci' => 'speech_assessment.expectations_post_ci',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function speechSelectOptions(): array
    {
        $map = [
            'communication_mood' => 'pre_op_speech_communication_mood',
            'iq' => 'pre_op_speech_iq',
            'cognitive_function' => 'pre_op_speech_cognitive_function',
            'true_word' => 'pre_op_speech_true_word',
            'phrases' => 'pre_op_speech_phrases',
            'assessment' => 'pre_op_speech_assessment',
            'speech_decision' => 'pre_op_speech_decision',
        ];

        $options = [];

        foreach ($map as $field => $category) {
            $options[$field] = PreOperationFieldSupport::selectOptions($category);
        }

        return $options;
    }

    /**
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

    /**
     * @param  list<array<string, mixed>>  $nodes
     * @return list<array<string, mixed>>
     */
    private function translateImagingTree(array $nodes): array
    {
        $out = [];

        foreach ($nodes as $node) {
            $item = [
                'key' => (string) ($node['key'] ?? ''),
                'label' => __((string) ($node['label_key'] ?? $node['key'] ?? '')),
            ];

            if (! empty($node['children']) && is_array($node['children'])) {
                $item['children'] = $this->translateImagingTree($node['children']);
            }

            $out[] = $item;
        }

        return $out;
    }
}
