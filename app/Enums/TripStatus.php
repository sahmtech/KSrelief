<?php

namespace App\Enums;

enum TripStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Planned => __('transportation.status.planned'),
            self::InProgress => __('transportation.status.in_progress'),
            self::Completed => __('transportation.status.completed'),
            self::Cancelled => __('transportation.status.cancelled'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Planned => 'badge-status--pending',
            self::InProgress => 'badge-status--active',
            self::Completed => 'badge-status--active',
            self::Cancelled => 'badge-status--inactive',
        };
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Planned => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }
}
