<?php

namespace App\Support;

use App\Models\Member;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\CampaignOperationDefaultService;
use App\Services\LookupService;
use App\Services\MedicalRecordService;

final class OperationFormMetaBuilder
{
    public function __construct(
        private readonly MedicalRecordService $recordService,
        private readonly LookupService $lookupService,
        private readonly CampaignOperationDefaultService $campaignOperationDefaultService,
    ) {}

    /**
     * Web parity for stage `operation` (Intra-Operative / During Surgery).
     *
     * @return array<string, mixed>
     */
    public function build(Patient $patient): array
    {
        $stageCode = 'operation';
        $fields = $this->recordService->getStageFields($stageCode);
        $teamMembers = $this->lookupService->getCampaignTeamMembers((int) $patient->campaign_id);
        $allCompanies = $this->lookupService->getImplantCompanies();
        $quickFillCompanies = $this->campaignOperationDefaultService->supportedCompanies();
        if ($quickFillCompanies->isEmpty()) {
            $quickFillCompanies = $allCompanies;
        }

        return [
            'stage_code' => $stageCode,
            'phase' => $this->resolvedPhase(),
            'title' => [
                'workflow' => __('workflow.title'),
                'stage' => PatientStage::displayNameForCode($stageCode),
                'intra_op' => __('workflow.phases.intra_op'),
            ],
            'field_order' => array_keys($fields),
            'fields' => $fields,
            'layout' => $this->layoutRows(),
            'team_members' => [
                'doctors' => $this->formatMembers($teamMembers['doctors'] ?? collect()),
            ],
            'implant_companies' => $this->formatCompanies($allCompanies),
            'quick_fill_companies' => $this->formatCompanies($quickFillCompanies),
            'insertion_approaches' => $this->lookupService->getInsertionApproaches()
                ->map(fn ($approach): array => [
                    'id' => $approach->id,
                    'name' => $approach->name,
                ])->values()->all(),
            'select_options' => [
                'side_of_surgery' => ScreeningFieldSupport::optionsFromKey('operation_side_of_surgery_options'),
                'insertion_depth' => OperationFieldSupport::insertionDepthOptions(),
                'intra_op_findings' => OperationFieldSupport::intraOpFindingsOptions(),
            ],
            'composites' => [
                'insertion_depth' => [
                    'shape' => ['selection' => 'string|null', 'note' => 'string'],
                    'partial_selection_code' => 'partial_insertion',
                    'note_visible_when_selection' => 'partial_insertion',
                    'legacy_field' => 'insertion_depth_note',
                ],
                'audio_test' => [
                    'shape' => ['metrics' => 'list<{key: string, value: string}>'],
                    'default_metric_keys' => OperationFieldSupport::AUDIO_TEST_KEYS,
                    'allow_extra_metric_rows' => true,
                    'legacy_field' => 'impedance_testing',
                    'legacy_maps_to_metric_key' => 'Impedance',
                ],
            ],
            'defaults_empty' => $this->emptyPayload(),
            'widgets' => $this->widgetMap(),
            'create_ui' => [
                'default_tab' => 'quick_fill',
                'tabs' => [
                    ['code' => 'quick_fill', 'label_key' => 'workflow.operation.tabs.quick_fill'],
                    ['code' => 'manual', 'label_key' => 'workflow.operation.tabs.manual_entry'],
                ],
                'tabs_create_only' => true,
                'edit_shows_manual_only' => true,
            ],
            'quick_fill' => [
                'get_path' => '/api/v1/patients/{patientId}/records/operation-quick-fill',
                'query' => ['implant_company_id' => 'required integer'],
                'company_list_key' => 'quick_fill_companies',
                'preset_apply' => [
                    'merge_into_fields' => true,
                    'strip_keys_before_save' => ['_apply_labels'],
                    'always_set_on_create' => ['operation_date'],
                ],
            ],
            'electrode_types' => [
                'depends_on' => 'implant_company_id',
                'get_path' => '/api/v1/patients/{patientId}/records/electrode-types',
                'query' => ['implant_company_id' => 'required integer'],
            ],
            'templates' => [
                'user_defaults' => [
                    'get_path' => '/api/v1/patients/{patientId}/records/operation-defaults',
                    'response_key' => 'data',
                    'may_include' => '_apply_labels',
                ],
                'campaign_defaults' => [
                    'get_path' => '/api/v1/patients/{patientId}/records/campaign-operation-defaults',
                    'query' => ['implant_company_id' => 'required integer'],
                    'response_key' => 'data',
                    'may_include' => '_apply_labels',
                ],
                'save_defaults' => [
                    'supported' => false,
                    'note' => 'Web POST save_operation_defaults on record store only.',
                ],
            ],
            'clinical_select' => [
                'categories' => array_keys(OperationFieldSupport::SELECT_CATEGORY_CONFIG),
                'inline_add_supported_on_web' => true,
                'inline_add_api' => null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvedPhase(): array
    {
        $phase = config('patient_clinical.phases.intra_op', []);

        return [
            'code' => 'intra_op',
            'label' => __($phase['label'] ?? 'workflow.phases.intra_op'),
            'color' => $phase['color'] ?? '#6AA84F',
            'background' => $phase['background'] ?? '#B6D7A8',
        ];
    }

    /**
     * Mirrors `resources/views/pages/patients/records/_operation_stage_fields.blade.php`.
     *
     * @return list<array{key: string, column: string, widget: string, section?: string, member_role?: string, depends_on?: string}>
     */
    private function layoutRows(): array
    {
        return [
            ['key' => 'operation_date', 'column' => 'half', 'widget' => 'date'],
            ['key' => 'surgeon', 'column' => 'half', 'widget' => 'member_select', 'member_role' => 'doctor'],
            ['key' => 'implant_company_id', 'column' => 'half', 'widget' => 'company_select'],
            ['key' => 'electrode_type_id', 'column' => 'half', 'widget' => 'electrode_select', 'depends_on' => 'implant_company_id'],
            ['key' => 'insertion_approach_id', 'column' => 'half', 'widget' => 'insertion_approach_select'],
            ['key' => 'side_of_surgery', 'column' => 'half', 'widget' => 'select'],
            ['key' => 'insertion_depth', 'column' => 'full', 'widget' => 'operation_insertion_depth', 'section' => 'divider'],
            ['key' => 'time_in_surgery', 'column' => 'half', 'widget' => 'time'],
            ['key' => 'time_out_surgery', 'column' => 'half', 'widget' => 'time'],
            ['key' => 'audio_test', 'column' => 'full', 'widget' => 'operation_audio_test', 'section' => 'divider'],
            ['key' => 'intra_op_findings', 'column' => 'full', 'widget' => 'operation_intra_op_findings'],
            ['key' => 'operation_notes', 'column' => 'full', 'widget' => 'textarea'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function widgetMap(): array
    {
        return [
            'operation_date' => 'date',
            'surgeon' => 'member_select',
            'implant_company_id' => 'company_select',
            'electrode_type_id' => 'electrode_select',
            'insertion_approach_id' => 'insertion_approach_select',
            'side_of_surgery' => 'select',
            'insertion_depth' => 'operation_insertion_depth',
            'time_in_surgery' => 'time',
            'time_out_surgery' => 'time',
            'audio_test' => 'operation_audio_test',
            'intra_op_findings' => 'operation_intra_op_findings',
            'operation_notes' => 'textarea',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(): array
    {
        return array_merge(
            [
                'operation_date' => now()->toDateString(),
                'side_of_surgery' => null,
            ],
            OperationFieldSupport::defaultTemplatePayload(),
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\ImplantCompany>  $companies
     * @return list<array{id: int, name: string, code: string, color: ?string}>
     */
    private function formatCompanies($companies): array
    {
        return $companies->map(fn ($company): array => [
            'id' => (int) $company->id,
            'name' => $company->name,
            'code' => (string) $company->code,
            'color' => $company->color ?? null,
        ])->values()->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Member>  $members
     * @return list<array{id: int, name: string, specialty: ?string}>
     */
    private function formatMembers($members): array
    {
        return $members->map(fn (Member $member): array => [
            'id' => (int) $member->id,
            'name' => $member->full_name,
            'specialty' => $member->specialty?->name,
        ])->values()->all();
    }
}
