<?php

namespace App\Models;

use App\Enums\PassengerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportationTripPassenger extends Model
{
    protected $fillable = [
        'trip_id',
        'passenger_type',
        'member_id',
        'patient_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'passenger_type' => PassengerType::class,
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TransportationTrip::class, 'trip_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function passengerName(): string
    {
        return match ($this->passenger_type) {
            PassengerType::Member => $this->member?->full_name ?? '—',
            PassengerType::Patient => $this->patient?->patient_name ?? '—',
            default => '—',
        };
    }

    public function passengerTypeLabel(): string
    {
        return $this->passenger_type?->label() ?? '—';
    }
}
