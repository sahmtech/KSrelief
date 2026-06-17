<?php

namespace App\Models;

use App\Enums\TripStatus;
use App\Enums\TripType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportationTrip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'trip_code',
        'trip_date',
        'departure_time',
        'arrival_time',
        'from_location_id',
        'to_location_id',
        'trip_type',
        'vehicle_number',
        'driver_name',
        'capacity',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
            'trip_type' => TripType::class,
            'status' => TripStatus::class,
            'capacity' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(TransportationLocation::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(TransportationLocation::class, 'to_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(TransportationTripPassenger::class, 'trip_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(TransportationTripStatusLog::class, 'trip_id');
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '—';
    }

    public function statusBadgeClass(): string
    {
        return $this->status?->badgeClass() ?? 'badge-status--inactive';
    }

    public function tripTypeLabel(): string
    {
        return $this->trip_type?->label() ?? '—';
    }

    public function departureTimeLabel(): string
    {
        return $this->formatTimeValue($this->departure_time);
    }

    public function arrivalTimeLabel(): string
    {
        return $this->formatTimeValue($this->arrival_time);
    }

    public function passengersCount(): int
    {
        return $this->passengers_count ?? $this->passengers()->count();
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [TripStatus::Planned, TripStatus::InProgress], true);
    }

    private function formatTimeValue(mixed $value): string
    {
        if (! filled($value)) {
            return '—';
        }

        return is_string($value) ? substr($value, 0, 5) : $value->format('H:i');
    }

    /**
     * @param  Builder<TransportationTrip>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('trip_code', 'like', "%{$term}%")
                ->orWhere('vehicle_number', 'like', "%{$term}%")
                ->orWhere('driver_name', 'like', "%{$term}%");
        });
    }

    /**
     * @param  Builder<TransportationTrip>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['campaign_id'] ?? null, fn (Builder $q, int $id) => $q->where('campaign_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('trip_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('trip_date', '<=', $date))
            ->when($filters['trip_type'] ?? null, fn (Builder $q, string $type) => $q->where('trip_type', $type))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['from_location_id'] ?? null, fn (Builder $q, int $id) => $q->where('from_location_id', $id))
            ->when($filters['to_location_id'] ?? null, fn (Builder $q, int $id) => $q->where('to_location_id', $id));
    }
}
