<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => __('users.status.active'),
            self::Inactive => __('users.status.inactive'),
            self::Suspended => __('users.status.suspended'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'badge-status--active',
            self::Inactive => 'badge-status--inactive',
            self::Suspended => 'badge-status--pending',
        };
    }
}
