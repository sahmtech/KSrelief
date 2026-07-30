<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\Member;
use App\Models\Patient;
use App\Models\PatientStage;
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
        'anesthesia' => ['npo_time', 'asa_score', 'anesthesia_type'],
        'operation' => ['operation_date', 'surgeon', 'implant_company_id', 'electrode_type_id', 'insertion_approach_id', 'side_of_surgery', 'insertion_depth', 'audio_test', 'intra_op_findings', 'operation_notes'],
        'follow_up' => ['clinical_assessment', 'audiology_assessment', 'speech_assessment', 'follow_up_notes'],
        'pre_operation' => ['physician_assessment', 'imaging_findings', 'audiology_decision', 'speech_assessment'],
        'post_operation' => ['physician_assessment', 'clinical_aud', 'counselling', 'post_op_notes'],
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
     *     stage_summaries: list<array{code: string, name: string, record_id: int, record_date: ?string, record_count: int, items: list<array{label: string, value: mixed, type?: string, field_definition?: array<string, mixed>, color?: ?string}>}>,
     *     record_overview: array{total: int, stages_with_data: int, has_history: bool},
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
            'record_overview' => $this->buildRecordOverview($patient),
        ];
    }

    /**
     * @return array{total: int, stages_with_data: int, has_history: bool}
     */
    private function buildRecordOverview(Patient $patient): array
    {
        $total = $patient->medicalRecords()->count();
        $stagesWithData = $this->recordService->getLatestRecordsByStage($patient)->count();

        return [
            'total' => $total,
            'stages_with_data' => $stagesWithData,
            'has_history' => $total > $stagesWithData,
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array{text: string, color: ?string, raw: mixed}
     */
    private function resolveBriefField(string $key, mixed $value, array $definition): array
    {
        $type = $definition['type'] ?? 'text';

        $compositeTypes = [
            'clinical_aud',
            'clinical_speech',
            'clinical_speech_followup',
            'imaging_findings',
            'expandable_checklist',
            'medical_history_screening',
            'follow_up_clinical_assessment',
            'follow_up_audiology_assessment',
            'follow_up_speech_assessment',
            'follow_up_notes',
            'pre_op_physician_assessment',
            'pre_op_audiology_decision',
            'pre_op_speech_assessment',
            'operation_insertion_depth',
            'operation_audio_test',
            'operation_intra_op_findings',
            'post_op_physician_assessment',
            'post_op_clinical_aud',
            'post_op_notes',
        ];

        if (in_array($type, $compositeTypes, true)) {
            return [
                'text' => ClinicalCompositeFields::present($key, $value, $definition),
                'color' => null,
                'raw' => $value,
            ];
        }

        if ($type === 'yes_no' || $type === 'select') {
            return [
                'text' => ClinicalCompositeFields::present($key, $value, $definition),
                'color' => null,
                'raw' => $value,
            ];
        }

        if ($type === 'member_select' && is_numeric($value)) {
            $member = Member::query()->find((int) $value);

            return [
                'text' => $member?->full_name ?? (string) $value,
                'color' => null,
                'raw' => $value,
            ];
        }

        $resolved = OperationFieldResolver::resolve($key, $value, $definition);

        return [
            'text' => $resolved['text'],
            'color' => $resolved['color'],
            'raw' => $value,
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
            $resolved = $this->resolveBriefField($key, $value, $definition);
            $items[] = [
                'label' => $definition['label'],
                'value' => in_array($type, ['clinical_aud', 'clinical_speech', 'clinical_speech_followup'], true) || in_array($type, $compositeComponentTypes, true) || $type === 'yes_no'
                    ? $resolved['raw']
                    : $resolved['text'],
                'type' => $type,
                'field_definition' => $definition,
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
                $resolved = $this->resolveBriefField($key, $value, $definition);
                $compositeTypes = ['clinical_aud', 'clinical_speech', 'clinical_speech_followup', 'imaging_findings', 'expandable_checklist', 'medical_history_screening', 'follow_up_clinical_assessment', 'follow_up_audiology_assessment', 'follow_up_speech_assessment', 'follow_up_notes', 'pre_op_physician_assessment', 'pre_op_audiology_decision', 'pre_op_speech_assessment', 'operation_insertion_depth', 'operation_audio_test', 'operation_intra_op_findings', 'post_op_physician_assessment', 'post_op_clinical_aud', 'post_op_notes'];

                $items[] = [
                    'label' => $definition['label'] ?? $key,
                    'value' => in_array($type, $compositeTypes, true) || $type === 'yes_no' ? $resolved['raw'] : $resolved['text'],
                    'type' => $type,
                    'field_definition' => $definition,
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
     * @return list<array{code: string, name: string, record_id: int, record_date: ?string, record_count: int, items: list<array{label: string, value: mixed, type?: string, field_definition?: array<string, mixed>, color?: ?string}>}>
     */
    private function buildStageSummaries(Patient $patient): array
    {
        $recordsByStage = $this->recordService->getLatestRecordsByStage($patient);
        $recordCountsByStage = $patient->medicalRecords()
            ->selectRaw('stage_id, count(*) as aggregate')
            ->groupBy('stage_id')
            ->pluck('aggregate', 'stage_id');
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
                $resolved = $this->resolveBriefField($key, $value, $definition);
                $compositeTypes = ['clinical_aud', 'clinical_speech', 'clinical_speech_followup', 'imaging_findings', 'expandable_checklist', 'medical_history_screening', 'follow_up_clinical_assessment', 'follow_up_audiology_assessment', 'follow_up_speech_assessment', 'follow_up_notes', 'pre_op_physician_assessment', 'pre_op_audiology_decision', 'pre_op_speech_assessment', 'operation_insertion_depth', 'operation_audio_test', 'operation_intra_op_findings', 'post_op_physician_assessment', 'post_op_clinical_aud', 'post_op_notes'];

                $items[] = [
                    'label' => $definition['label'],
                    'value' => in_array($type, $compositeTypes, true) ? $resolved['raw'] : $resolved['text'],
                    'type' => $type,
                    'field_definition' => $definition,
                    'color' => $resolved['color'],
                ];
            }

            if ($items !== []) {
                $summaries[] = [
                    'code' => $stageCode,
                    'name' => $record->stage?->displayName() ?? PatientStage::displayNameForCode($stageCode),
                    'record_id' => $record->id,
                    'record_date' => $record->record_date?->format('d M Y'),
                    'record_count' => (int) ($recordCountsByStage[$record->stage_id] ?? 1),
                    'items' => $items,
                ];
            }
        }

        return $summaries;
    }
}
