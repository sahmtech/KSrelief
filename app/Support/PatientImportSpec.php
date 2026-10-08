<?php

namespace App\Support;

use App\Enums\Gender;
use App\Exports\PatientTemplateExport;

/**
 * Machine-readable import contract for API + mobile clients.
 */
final class PatientImportSpec
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(): array
    {
        $templateColumns = PatientTemplateExport::columnHeadings();
        $requiredColumns = PatientTemplateExport::requiredColumnHeadings();

        $columns = [];

        foreach ($templateColumns as $column) {
            $columns[] = [
                'key' => $column,
                'label' => __('patients.import.fields.'.$column),
                'required_on_row' => in_array($column, $requiredColumns, true),
                'validation_hint' => __('patients.import.column_hints.'.$column),
            ];
        }

        return [
            'template' => [
                'sheet_title' => 'Patients',
                'header_row' => 1,
                'data_starts_at_row' => 2,
                'single_sheet_only' => true,
                'column_order_fixed' => true,
                'columns' => $columns,
                'column_keys_in_order' => $templateColumns,
                'required_row_fields' => $requiredColumns,
            ],
            'upload' => [
                'allowed_extensions' => ['xlsx', 'xls', 'csv'],
                'allowed_mimes' => ['xlsx', 'xls', 'csv'],
                'max_file_size_kb' => 20480,
                'multipart_field' => 'file',
                'required_fields' => ['file', 'campaign_id'],
                'optional_fields' => ['notes'],
            ],
            'defaults_on_approve' => [
                'date_of_birth' => config('patient_import.default_date_of_birth', '2000-01-01'),
                'gender' => config('patient_import.default_gender', Gender::Male->value),
                'eligibility_status_code' => 'accepted',
                'admission_status' => 'not_admitted',
            ],
            'processing' => [
                'sync_by_default' => (bool) config('patient_import.sync_processing', true),
                'env_flag' => 'PATIENT_IMPORT_SYNC',
            ],
            'batch_statuses' => [
                ['code' => 'uploaded', 'label' => __('patients.import.status.uploaded')],
                ['code' => 'processing', 'label' => __('patients.import.status.processing')],
                ['code' => 'review', 'label' => __('patients.import.status.review')],
                ['code' => 'approved', 'label' => __('patients.import.status.approved')],
                ['code' => 'completed', 'label' => __('patients.import.status.completed')],
                ['code' => 'failed', 'label' => __('patients.import.status.failed')],
            ],
            'instructions' => __('patients.import.instructions'),
            'default_notes' => __('patients.import.default_notes'),
            'permissions' => [
                'upload_and_template' => 'patient.import_excel',
                'history_and_review' => 'patient.import_history',
                'approve' => 'patient.import_approve',
            ],
            'gender_values' => collect(Gender::cases())->map(fn (Gender $g) => [
                'value' => $g->value,
                'label' => $g->label(),
            ])->values()->all(),
        ];
    }
}
