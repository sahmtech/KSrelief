<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Support\ClinicalCompositeFields;
use App\Support\OperationFieldResolver;
use App\Support\PatientClinicalFieldRegistry;

class PatientBriefService
{
    /** @var list<string> */
    private const PHASE_ORDER = ['pre_op', 'intra_op', 'screening', 'post_op', 'follow_up'];

    /** @var list<string> */
    private const PRIORITY_SCREENING_KEYS = [
        'consent',
        'imaging_findings',
        'cochlear_diameter',
        'surgical_consideration',
        'medical_history',
        'clinical_aud',
        'clinical_speech',
        'audiology_link',
    ];

    /** @var list<string> */
    private const PRIORITY_STAGE_KEYS = [
        'anesthesia' => ['npo_time', 'asa_score', 'anesthesia_type', 'readiness_status'],
        'operation' => ['surgeon', 'implant_company_id', 'electrode_type_id', 'insertion_approach_id', 'intra_op_findings'],
        'follow_up' => ['clinical_aud', 'clinical_speech'],
        'post_operation' => ['clinical_aud', 'post_op_exam', 'pain_score', 'swelling_size'],
    ];

    /** @var list<string> */
    private const STAGE_ORDER = ['pre_operation', 'anesthesia', 'admission', 'operation', 'post_operation', 'follow_up'];

    public function __construct(
        private readonly MedicalRecordService $recordService,
        private readonly PatientClinicalFieldRegistry $fieldRegistry,
    ) {}

    /**
     * @return array{
     *     surgery_context: list<array{label: string, value: string, highlight?: bool}>,
     *     demographics: list<array{label: string, value: string}>,
     *     priority_clinical: list<array{label: string, value: string, type?: string}>,
     *     phases: array<string, array{label: string, color: string, background: string, items: list<array{label: string, value: string, source: string, type?: string}>}>,
     *     stage_summaries: list<array{code: string, name: string, items: list<array{label: string, value: string}>}>,
     * }
     */
    public function build(Patient $patient, ?array $clinicalProfile): array
    {
        $priorityClinical = $this->buildPriorityClinical($patient, $clinicalProfile);

        return [
            'surgery_context' => $this->buildSurgeryContext($patient, $priorityClinical),
            'demographics' => $this->buildDemographics($patient),
            'priority_clinical' => $priorityClinical,
            'phases' => $this->orderPhases($clinicalProfile['phases'] ?? []),
            'stage_summaries' => $this->buildStageSummaries($patient),
        ];
    }

    /**
     * @param  list<array{label: string, value: string, type?: string}>  $priorityClinical
     * @return list<array{label: string, value: string, highlight?: bool}>
     */
    private function buildSurgeryContext(Patient $patient, array $priorityClinical): array
    {
        $items = [];

        if (filled($patient->surgery_day_number)) {
            $items[] = [
                'label' => __('patients.fields.surgery_day_number'),
                'value' => $patient->surgeryDayLabel(),
                'highlight' => true,
            ];
        }

        if (filled($patient->rank)) {
            $items[] = [
                'label' => __('patients.fields.rank'),
                'value' => (string) $patient->rank,
                'highlight' => true,
            ];
        }

        if (filled($patient->surgical_side)) {
            $items[] = [
                'label' => __('patients.fields.surgical_side'),
                'value' => $patient->surgicalSideLabel(),
                'highlight' => true,
            ];
        }

        if ($patient->currentStage) {
            $items[] = [
                'label' => __('patients.fields.current_stage'),
                'value' => $patient->currentStage->name,
            ];
        }

        $items[] = [
            'label' => __('patients.fields.admission_status'),
            'value' => $patient->admissionLabel(),
        ];

        if ($patient->eligibilityStatus) {
            $items[] = [
                'label' => __('patients.fields.eligibility_status'),
                'value' => $patient->eligibilityStatus->name,
            ];
        }

        foreach (array_slice($priorityClinical, 0, 4) as $item) {
            $items[] = [
                'label' => $item['label'],
                'value' => $item['value'],
                'highlight' => true,
            ];
        }

        return $items;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function buildDemographics(Patient $patient): array
    {
        $items = [
            ['label' => __('patients.fields.patient_name'), 'value' => $patient->patient_name],
        ];

        if ($patient->file_number) {
            $items[] = ['label' => __('patients.fields.file_number'), 'value' => $patient->file_number];
        }

        $items[] = ['label' => __('patients.fields.age'), 'value' => $patient->ageLabel()];

        if ($patient->gender) {
            $items[] = ['label' => __('patients.fields.gender'), 'value' => $patient->gender->label()];
        }

        if (filled($patient->height_cm)) {
            $items[] = ['label' => __('patients.fields.height_cm'), 'value' => $patient->heightLabel()];
        }

        if (filled($patient->weight_kg)) {
            $items[] = ['label' => __('patients.fields.weight_kg'), 'value' => $patient->weightLabel()];
        }

        if ($patient->campaign) {
            $items[] = ['label' => __('patients.fields.campaign'), 'value' => $patient->campaign->name];
        }

        if (filled($patient->contact_number)) {
            $items[] = ['label' => __('patients.fields.contact_number'), 'value' => $patient->contact_number];
        }

        return $items;
    }

    /**
     * @return list<array{label: string, value: string, type?: string}>
     */
    private function buildPriorityClinical(Patient $patient, ?array $clinicalProfile): array
    {
        $items = [];
        $seen = [];

        foreach (self::PRIORITY_SCREENING_KEYS as $key) {
            $value = $patient->screening($key);
            $definition = $this->fieldRegistry->screeningFields()[$key] ?? null;

            if (! $definition) {
                continue;
            }

            if (! ClinicalCompositeFields::hasContent($key, $value, $definition)) {
                continue;
            }

            $type = $definition['type'] ?? 'text';
            $compositeComponentTypes = ['imaging_findings', 'expandable_checklist', 'medical_history_screening'];
            $items[] = [
                'label' => $definition['label'],
                'value' => in_array($type, ['clinical_aud', 'clinical_speech', 'clinical_speech_followup'], true)
                    ? ClinicalCompositeFields::present($key, $value, $definition)
                    : (in_array($type, $compositeComponentTypes, true) ? $value : (string) $value),
                'type' => $type,
            ];
            $seen[$key] = true;
        }

        if (filled($patient->surgical_side)) {
            $items[] = [
                'label' => __('patients.fields.surgical_side'),
                'value' => $patient->surgicalSideLabel(),
                'type' => 'text',
            ];
        }

        $recordsByStage = $this->recordService->getLatestRecordsByStage($patient);

        foreach (self::PRIORITY_STAGE_KEYS as $stageCode => $fieldKeys) {
            /** @var MedicalRecord|null $record */
            $record = $recordsByStage->first(fn (MedicalRecord $r): bool => ($r->stage?->code ?? '') === $stageCode);

            if (! $record) {
                continue;
            }

            $fields = $this->fieldRegistry->getStageFields($stageCode);

            foreach ($fieldKeys as $key) {
                if (isset($seen[$key])) {
                    continue;
                }

                $value = $record->field($key);
                $definition = $fields[$key] ?? null;

                if (! $definition || ! ClinicalCompositeFields::hasContent($key, $value, $definition)) {
                    continue;
                }

                $type = $definition['type'] ?? 'text';

                if (in_array($type, ['clinical_aud', 'clinical_speech', 'clinical_speech_followup', 'imaging_findings', 'expandable_checklist', 'medical_history_screening'], true)) {
                    $resolved = ['text' => ClinicalCompositeFields::present($key, $value, $definition), 'color' => null];
                } else {
                    $resolved = OperationFieldResolver::resolve($key, $value, $definition);
                }

                $items[] = [
                    'label' => $definition['label'] ?? $key,
                    'value' => $resolved['text'],
                    'type' => $type,
                    'color' => $resolved['color'],
                ];
                $seen[$key] = true;
            }

            if ($stageCode === 'operation') {
                $legacyMap = [
                    'electrode_type' => __('workflow.fields.electrode_type'),
                    'insertion_type' => __('workflow.fields.insertion_approach'),
                ];

                foreach ($legacyMap as $legacyKey => $label) {
                    if (isset($seen[$legacyKey])) {
                        continue;
                    }

                    $value = $record->field($legacyKey);
                    if (! filled($value)) {
                        continue;
                    }

                    $items[] = [
                        'label' => $label,
                        'value' => (string) $value,
                        'type' => 'text',
                    ];
                    $seen[$legacyKey] = true;
                }
            }
        }

        if ($clinicalProfile) {
            foreach ($clinicalProfile['phases']['pre_op']['items'] ?? [] as $item) {
                $label = $item['label'] ?? '';
                if ($label === '' || collect($items)->contains('label', $label)) {
                    continue;
                }

                $items[] = [
                    'label' => $label,
                    'value' => $item['value'],
                    'type' => $item['type'] ?? 'text',
                ];
            }
        }

        return array_slice($items, 0, 14);
    }

    /**
     * @param  array<string, array<string, mixed>>  $phases
     * @return array<string, array<string, mixed>>
     */
    private function orderPhases(array $phases): array
    {
        $ordered = [];

        foreach (self::PHASE_ORDER as $code) {
            if (! empty($phases[$code]['items'])) {
                $ordered[$code] = $phases[$code];
            }
        }

        return $ordered;
    }

    /**
     * @return list<array{code: string, name: string, items: list<array{label: string, value: string}>}>
     */
    private function buildStageSummaries(Patient $patient): array
    {
        $recordsByStage = $this->recordService->getLatestRecordsByStage($patient);
        $summaries = [];

        foreach (self::STAGE_ORDER as $stageCode) {
            /** @var MedicalRecord|null $record */
            $record = $recordsByStage->first(fn (MedicalRecord $r): bool => ($r->stage?->code ?? '') === $stageCode);

            if (! $record) {
                continue;
            }

            $fields = $this->fieldRegistry->getStageFields($stageCode);
            $items = [];

            foreach ($fields as $key => $definition) {
                $value = $record->field($key);
                if (! ClinicalCompositeFields::hasContent($key, $value, $definition)) {
                    continue;
                }

                $type = $definition['type'] ?? 'text';
                $resolved = in_array($type, ['clinical_aud', 'clinical_speech', 'clinical_speech_followup', 'imaging_findings', 'expandable_checklist', 'medical_history_screening'], true)
                    ? ['text' => ClinicalCompositeFields::present($key, $value, $definition), 'color' => null]
                    : OperationFieldResolver::resolve($key, $value, $definition);

                $items[] = [
                    'label' => $definition['label'],
                    'value' => $resolved['text'],
                    'color' => $resolved['color'],
                ];
            }

            if ($items !== []) {
                $summaries[] = [
                    'code' => $stageCode,
                    'name' => $record->stage?->name ?? ucfirst(str_replace('_', ' ', $stageCode)),
                    'items' => $items,
                ];
            }
        }

        return $summaries;
    }
}
