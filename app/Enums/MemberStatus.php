<?php

namespace App\Enums;

enum MemberStatus: string
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
            self::Active => __('members.status.active'),
            self::Inactive => __('members.status.inactive'),
            self::Suspended => __('members.status.suspended'),
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
