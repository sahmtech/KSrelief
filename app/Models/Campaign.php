<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'objective',
        'target_group',
        'country_id',
        'city_id',
        'specialty_id',
        'start_date',
        'end_date',
        'shifts_count',
        'expected_patients',
        'campaign_status_id',
        'description',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'shifts_count' => 'integer',
            'expected_patients' => 'integer',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function campaignStatus(): BelongsTo
    {
        return $this->belongsTo(CampaignStatusRecord::class, 'campaign_status_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CampaignUser::class);
    }

    public function staffMembers(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'campaign_member')
            ->withPivot(['assigned_role', 'assigned_from', 'assigned_to', 'notes', 'created_by', 'created_at'])
            ->withTimestamps();
    }

    public function campaignMemberAssignments(): HasMany
    {
        return $this->hasMany(CampaignMember::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function transportationTrips(): HasMany
    {
        return $this->hasMany(TransportationTrip::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function statusLabel(): string
    {
        return $this->campaignStatus?->label() ?? '—';
    }

    public function statusBadgeClass(): string
    {
        return $this->campaignStatus?->badgeClass() ?? 'badge-status--inactive';
    }

    public function isTerminalStatus(): bool
    {
        return $this->campaignStatus?->isTerminal() ?? false;
    }

    public function campaignDaysCount(): int
    {
        if ($this->start_date && $this->end_date) {
            return self::daysBetween($this->start_date, $this->end_date);
        }

        return (int) $this->shifts_count;
    }

    public static function daysBetween(Carbon|string $startDate, Carbon|string $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return $start->diffInDays($end) + 1;
    }

    /**
     * @param  Builder<Campaign>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('objective', 'like', "%{$term}%")
                ->orWhere('target_group', 'like', "%{$term}%");
        });
    }

    /**
     * @param  Builder<Campaign>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, function (Builder $q, string $statusCode): void {
                $q->whereHas('campaignStatus', fn (Builder $sq) => $sq->where('code', $statusCode));
            })
            ->when($filters['campaign_status_id'] ?? null, fn (Builder $q, int $statusId) => $q->where('campaign_status_id', $statusId))
            ->when($filters['country_id'] ?? null, fn (Builder $q, int $countryId) => $q->where('country_id', $countryId))
            ->when($filters['city_id'] ?? null, fn (Builder $q, int $cityId) => $q->where('city_id', $cityId))
            ->when($filters['specialty_id'] ?? null, fn (Builder $q, int $specialtyId) => $q->where('specialty_id', $specialtyId))
            ->when($filters['start_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('start_date', '>=', $date))
            ->when($filters['end_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('end_date', '<=', $date));
    }

    /**
     * @param  Builder<Campaign>  $query
     */
    public function scopeSortable(Builder $query, ?string $sort, ?string $direction = 'desc'): Builder
    {
        $allowed = [
            'name',
            'start_date',
            'end_date',
            'campaign_status_id',
            'shifts_count',
            'expected_patients',
            'created_at',
        ];

        $sort = in_array($sort, $allowed, true) ? $sort : 'created_at';
        $direction = strtolower((string) $direction) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'status') {
            return $query
                ->leftJoin('campaign_statuses', 'campaigns.campaign_status_id', '=', 'campaign_statuses.id')
                ->orderBy('campaign_statuses.name', $direction)
                ->select('campaigns.*');
        }

        return $query->orderBy($sort, $direction);
    }
}
