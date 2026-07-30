<?php

namespace App\Support;

final class MedicalRecordExportFormatter
{
    /**
     * @param  array<string, mixed>  $fieldDefinition
     */
    public static function format(string $fieldKey, mixed $value, array $fieldDefinition): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $type = $fieldDefinition['type'] ?? 'text';

        if ($type === 'member_select') {
            return MedicalRecordFieldPresenter::display($fieldKey, $value, $fieldDefinition);
        }

        if (in_array($type, ['company_select', 'electrode_select', 'insertion_approach_select'], true)) {
            return OperationFieldResolver::resolve($fieldKey, $value, $fieldDefinition)['text'];
        }

        return ClinicalCompositeFields::present($fieldKey, $value, $fieldDefinition);
    }
}
