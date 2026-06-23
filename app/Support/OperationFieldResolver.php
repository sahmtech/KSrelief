<?php

namespace App\Support;

use App\Models\ImplantCompany;
use App\Models\ImplantElectrodeType;
use App\Models\InsertionApproach;

final class OperationFieldResolver
{
    /**
     * @param  array<string, mixed>  $definition
     * @return array{text: string, color: ?string, type: string}
     */
    public static function resolve(string $fieldKey, mixed $value, array $definition = []): array
    {
        $type = $definition['type'] ?? 'text';

        if ($type === 'company_select' || $fieldKey === 'implant_company_id') {
            return self::resolveCompany($value);
        }

        if ($type === 'electrode_select' || $fieldKey === 'electrode_type_id') {
            return self::resolveElectrode($value);
        }

        if ($type === 'insertion_approach_select' || $fieldKey === 'insertion_approach_id') {
            return self::resolveInsertionApproach($value);
        }

        return [
            'text' => (string) $value,
            'color' => null,
            'type' => $type,
        ];
    }

    /**
     * @return array{text: string, color: ?string, type: string}
     */
    private static function resolveCompany(mixed $value): array
    {
        if (! is_numeric($value)) {
            return ['text' => (string) $value, 'color' => null, 'type' => 'company_select'];
        }

        $company = ImplantCompany::query()->find((int) $value);

        return [
            'text' => $company?->name ?? (string) $value,
            'color' => $company?->color,
            'type' => 'company_select',
        ];
    }

    /**
     * @return array{text: string, color: ?string, type: string}
     */
    private static function resolveElectrode(mixed $value): array
    {
        if (! is_numeric($value)) {
            return ['text' => (string) $value, 'color' => null, 'type' => 'electrode_select'];
        }

        $electrode = ImplantElectrodeType::query()->with('company')->find((int) $value);

        return [
            'text' => $electrode?->name ?? (string) $value,
            'color' => $electrode?->company?->color,
            'type' => 'electrode_select',
        ];
    }

    /**
     * @return array{text: string, color: ?string, type: string}
     */
    private static function resolveInsertionApproach(mixed $value): array
    {
        if (! is_numeric($value)) {
            return ['text' => (string) $value, 'color' => null, 'type' => 'insertion_approach_select'];
        }

        $approach = InsertionApproach::query()->find((int) $value);

        return [
            'text' => $approach?->name ?? (string) $value,
            'color' => null,
            'type' => 'insertion_approach_select',
        ];
    }
}
