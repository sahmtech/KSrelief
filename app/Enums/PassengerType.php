<?php

namespace App\Enums;

enum PassengerType: string
{
    case Member = 'member';
    case Patient = 'patient';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Member => __('transportation.passenger_type.member'),
            self::Patient => __('transportation.passenger_type.patient'),
        };
    }
}
