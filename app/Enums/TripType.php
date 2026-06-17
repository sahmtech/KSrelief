<?php

namespace App\Enums;

enum TripType: string
{
    case PatientTransport = 'patient_transport';
    case MemberTransport = 'member_transport';
    case MixedTransport = 'mixed_transport';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PatientTransport => __('transportation.trip_type.patient_transport'),
            self::MemberTransport => __('transportation.trip_type.member_transport'),
            self::MixedTransport => __('transportation.trip_type.mixed_transport'),
        };
    }
}
