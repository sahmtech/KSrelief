<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'member_id',
        'attendance_date',
        'shift_number',
        'check_in',
        'check_out',
        'attendance_status_id',
        'worked_minutes',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'shift_number' => 'integer',
            'worked_minutes' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function attendanceStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function workedHoursLabel(): string
    {
        if ($this->worked_minutes === null) {
            return '—';
        }

        $hours = intdiv($this->worked_minutes, 60);
        $minutes = $this->worked_minutes % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dm', $hours, $minutes);
        }

        return sprintf('%dm', $minutes);
    }

    public function checkInLabel(): string
    {
        return $this->formatTimeValue($this->check_in);
    }

    public function checkOutLabel(): string
    {
        return $this->formatTimeValue($this->check_out);
    }

    private function formatTimeValue(mixed $value): string
    {
        if (! filled($value)) {
            return '—';
        }

        return is_string($value)
            ? substr($value, 0, 5)
            : $value->format('H:i');
    }

    /**
     * @param  Builder<Attendance>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        return $query->whereHas('member', function (Builder $q) use ($term): void {
            $q->where('full_name', 'like', "%{$term}%")
                ->orWhere('mobile', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * @param  Builder<Attendance>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['campaign_id'] ?? null, fn (Builder $q, int $id) => $q->where('campaign_id', $id))
            ->when($filters['date_from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('attendance_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('attendance_date', '<=', $date))
            ->when($filters['shift_number'] ?? null, fn (Builder $q, int $shift) => $q->where('shift_number', $shift))
            ->when($filters['attendance_status_id'] ?? null, fn (Builder $q, int $id) => $q->where('attendance_status_id', $id))
            ->when($filters['member_role_id'] ?? null, function (Builder $q, int $roleId): void {
                $q->whereHas('member', fn (Builder $mq) => $mq->where('member_role_id', $roleId));
            })
            ->when($filters['specialty_id'] ?? null, function (Builder $q, int $specialtyId): void {
                $q->whereHas('member', fn (Builder $mq) => $mq->where('specialty_id', $specialtyId));
            });
    }
}
