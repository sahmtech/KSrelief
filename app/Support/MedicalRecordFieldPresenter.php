<?php

namespace App\Support;

use App\Models\Member;

final class MedicalRecordFieldPresenter
{
    /**
     * Resolve a stored field value for display.
     *
     * @param  array<string, mixed>  $fieldDef
     */
    public static function display(string $fieldKey, mixed $value, array $fieldDef): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $type = $fieldDef['type'] ?? 'text';

        if ($type === 'member_select') {
            $member = Member::query()->find($value);

            return $member?->full_name ?? (string) $value;
        }

        if ($type === 'select' && isset($fieldDef['options'][$value])) {
            return (string) $fieldDef['options'][$value];
        }

        return (string) $value;
    }
}
