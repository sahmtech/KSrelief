<?php

namespace App\Support;

use App\Models\Member;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\LookupService;
use App\Services\MedicalRecordService;

final class AnesthesiaFormMetaBuilder
{
    public function __construct(
        private readonly MedicalRecordService $recordService,
        private readonly LookupService $lookupService,
    ) {}

    /**
     * Web parity for stage `anesthesia` (generic stage_fields renderer).
     *
     * @return array<string, mixed>
     */
    public function build(Patient $patient): array
    {
        $stageCode = 'anesthesia';
        $fields = $this->recordService->getStageFields($stageCode);
        $teamMembers = $this->lookupService->getCampaignTeamMembers((int) $patient->campaign_id);

        return [
            'stage_code' => $stageCode,
            'phase' => $this->resolvedPhase(),
            'title' => [
                'workflow' => __('workflow.title'),
                'stage' => PatientStage::displayNameForCode($stageCode),
            ],
            'field_order' => array_keys($fields),
            'fields' => $fields,
            'layout' => $this->layoutRows(),
            'team_members' => [
                'doctors' => $this->formatMembers($teamMembers['doctors'] ?? collect()),
                'specialists' => $this->formatMembers($teamMembers['specialists'] ?? collect()),
                'coordinators' => $this->formatMembers($teamMembers['coordinators'] ?? collect()),
            ],
            'defaults_empty' => $this->emptyPayload(),
            'widgets' => $this->widgetMap(),
            'templates' => [
                'supported' => false,
                'note' => 'Anesthesia has no user/campaign template endpoints on web.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvedPhase(): array
    {
        $phase = config('patient_clinical.phases.pre_op', []);

        return [
            'code' => 'pre_op',
            'label' => __($phase['label'] ?? 'workflow.phases.pre_op'),
            'color' => $phase['color'] ?? '#FFD966',
            'background' => $phase['background'] ?? '#FFF2CC',
        ];
    }

    /**
     * Mirrors `resources/views/pages/patients/records/_stage_fields.blade.php` column rules.
     *
     * @return list<array{key: string, column: string, widget: string, member_role?: string}>
     */
    private function layoutRows(): array
    {
        return [
            ['key' => 'attending_doctor', 'column' => 'half', 'widget' => 'member_select', 'member_role' => 'doctor'],
            ['key' => 'anesthesia_type', 'column' => 'half', 'widget' => 'text'],
            ['key' => 'npo_time', 'column' => 'half', 'widget' => 'text'],
            ['key' => 'asa_score', 'column' => 'half', 'widget' => 'text'],
            ['key' => 'weight', 'column' => 'half', 'widget' => 'number'],
            ['key' => 'anesthesia_notes', 'column' => 'full', 'widget' => 'textarea'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function widgetMap(): array
    {
        return [
            'attending_doctor' => 'member_select',
            'anesthesia_type' => 'text',
            'npo_time' => 'text',
            'asa_score' => 'text',
            'weight' => 'number',
            'anesthesia_notes' => 'textarea',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(): array
    {
        return [
            'attending_doctor' => null,
            'anesthesia_type' => '',
            'npo_time' => '',
            'asa_score' => '',
            'weight' => null,
            'anesthesia_notes' => '',
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Member>  $members
     * @return list<array{id: int, name: string, specialty: ?string}>
     */
    private function formatMembers($members): array
    {
        return $members->map(fn (Member $member) => [
            'id' => $member->id,
            'name' => $member->full_name ?: trim($member->first_name.' '.$member->last_name),
            'specialty' => $member->specialty?->name,
        ])->values()->all();
    }
}
