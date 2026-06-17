<?php

namespace App\Enums;

enum ActivityParticipationStatus: string
{
    case Registered = 'registered';
    case Attended = 'attended';
    case Absent = 'absent';
    case Cancelled = 'cancelled';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Registered => __('activities.participation.registered'),
            self::Attended => __('activities.participation.attended'),
            self::Absent => __('activities.participation.absent'),
            self::Cancelled => __('activities.participation.cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Registered => 'badge-status--pending',
            self::Attended => 'badge-status--active',
            self::Absent => 'badge-status--inactive',
            self::Cancelled => 'badge-status--inactive',
        };
    }
}
