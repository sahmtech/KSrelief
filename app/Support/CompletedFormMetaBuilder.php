<?php

namespace App\Support;

use App\Models\PatientStage;
use App\Services\MedicalRecordService;

final class CompletedFormMetaBuilder
{
    public function __construct(
        private readonly MedicalRecordService $recordService,
    ) {}

    /**
     * Web parity for stage `completed` — header-only record (no stage_fields in config).
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $stageCode = 'completed';
        $fields = $this->recordService->getStageFields($stageCode);

        return [
            'stage_code' => $stageCode,
            'display_name' => PatientStage::displayNameForCode($stageCode),
            'title' => [
                'workflow' => __('workflow.title'),
                'stage' => PatientStage::displayNameForCode($stageCode),
            ],
            'field_order' => array_keys($fields),
            'fields' => $fields,
            'form_ui' => [
                'stage_fields_panel' => false,
                'reason' => 'No entries in config/patient_clinical.php → stage_fields.completed; web _stage_fields.blade.php renders nothing when stageFields is empty.',
                'header_fields' => [
                    ['key' => 'stage_id', 'required' => true, 'widget' => 'stage_select'],
                    ['key' => 'record_date', 'required' => true, 'widget' => 'date'],
                    ['key' => 'notes', 'required' => false, 'widget' => 'textarea', 'rows' => 3],
                ],
            ],
            'stage_presentation' => [
                'badge_color_default' => '#22C55E',
                'badge_color_source' => 'patient_stages.color where code = completed (schema.stages[].color)',
            ],
            'defaults_empty' => [],
            'payload_empty' => [
                'fields' => [],
            ],
            'widgets' => [],
            'templates' => [
                'supported' => false,
                'note' => 'No defaults or template endpoints for completed.',
            ],
            'show_page' => [
                'clinical_fields_card' => false,
                'general_notes_card' => true,
                'empty_fields_message_key' => 'workflow.records.no_stage_fields_saved',
            ],
            'workflow_context' => [
                'terminal_stage' => true,
                'patient_stats' => 'Patients with current_stage code completed count toward dashboard completed_patients KPI.',
                'related_api' => 'POST /api/v1/patients/{id}/workflow/stage to move patient to completed (separate from medical record create).',
            ],
        ];
    }
}
