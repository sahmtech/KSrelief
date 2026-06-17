<?php

namespace App\Models;

use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportationTripStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'trip_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => TripStatus::class,
            'new_status' => TripStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(TransportationTrip::class, 'trip_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
