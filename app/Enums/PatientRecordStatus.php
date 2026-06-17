<?php

namespace App\Enums;

enum PatientRecordStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Archived = 'archived';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => __('patients.record_status.active'),
            self::Closed => __('patients.record_status.closed'),
            self::Archived => __('patients.record_status.archived'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'badge-status--active',
            self::Closed => 'badge-status--secondary',
            self::Archived => 'badge-status--inactive',
        };
    }
}
