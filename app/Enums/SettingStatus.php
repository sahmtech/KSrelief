<?php

namespace App\Enums;

enum SettingStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => __('settings.status.active'),
            self::Inactive => __('settings.status.inactive'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'badge-status--active',
            self::Inactive => 'badge-status--inactive',
        };
    }
}
