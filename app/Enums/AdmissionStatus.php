<?php

namespace App\Enums;

enum AdmissionStatus: string
{
    case Admitted = 'admitted';
    case NotAdmitted = 'not_admitted';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Admitted => __('patients.admission.admitted'),
            self::NotAdmitted => __('patients.admission.not_admitted'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Admitted => 'badge-status--active',
            self::NotAdmitted => 'badge-status--inactive',
        };
    }
}
