<?php

namespace App\Models;

use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'activity_type_id',
        'patient_stage_id',
        'title',
        'description',
        'activity_date',
        'start_time',
        'end_time',
        'location',
        'status',
        'max_participants',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'status' => ActivityStatus::class,
            'max_participants' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function patientStage(): BelongsTo
    {
        return $this->belongsTo(PatientStage::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ActivityParticipant::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ActivityStatusLog::class);
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '—';
    }

    public function statusBadgeClass(): string
    {
        return $this->status?->badgeClass() ?? 'badge-status--inactive';
    }

    public function startTimeLabel(): string
    {
        return $this->formatTimeValue($this->start_time);
    }

    public function endTimeLabel(): string
    {
        return $this->formatTimeValue($this->end_time);
    }

    public function participantsCount(): int
    {
        return $this->participants_count ?? $this->participants()->count();
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [ActivityStatus::Planned, ActivityStatus::InProgress], true);
    }

    public function calendarColor(): string
    {
        return $this->activityType?->color ?? '#3B82F6';
    }

    public function calendarStart(): string
    {
        $date = $this->activity_date->format('Y-m-d');
        $start = $this->startTimeLabel();

        return "{$date}T{$start}:00";
    }

    public function calendarEnd(): string
    {
        $date = $this->activity_date->format('Y-m-d');
        $end = $this->endTimeLabel();

        return "{$date}T{$end}:00";
    }

    private function formatTimeValue(mixed $value): string
    {
        if (! filled($value)) {
            return '00:00';
        }

        return is_string($value) ? substr($value, 0, 5) : $value->format('H:i');
    }

    /**
     * @param  Builder<Activity>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    /**
     * @param  Builder<Activity>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['campaign_id'] ?? null, fn (Builder $q, int $id) => $q->where('campaign_id', $id))
            ->when($filters['activity_type_id'] ?? null, fn (Builder $q, int $id) => $q->where('activity_type_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('activity_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('activity_date', '<=', $date))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status));
    }

    /**
     * @param  Builder<Activity>  $query
     */
    public function scopeBetweenDates(Builder $query, string $start, string $end): Builder
    {
        return $query->whereDate('activity_date', '>=', $start)
            ->whereDate('activity_date', '<=', $end);
    }
}
