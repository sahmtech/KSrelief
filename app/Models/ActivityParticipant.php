<?php

namespace App\Models;

use App\Enums\ActivityParticipationStatus;
use App\Enums\PassengerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityParticipant extends Model
{
    protected $fillable = [
        'activity_id',
        'participant_type',
        'member_id',
        'patient_id',
        'attendance_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'participant_type' => PassengerType::class,
            'attendance_status' => ActivityParticipationStatus::class,
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function participantName(): string
    {
        return match ($this->participant_type) {
            PassengerType::Member => $this->member?->full_name ?? '—',
            PassengerType::Patient => $this->patient?->patient_name ?? '—',
            default => '—',
        };
    }

    public function participantTypeLabel(): string
    {
        return $this->participant_type?->label() ?? '—';
    }

    public function attendanceStatusLabel(): string
    {
        return $this->attendance_status?->label() ?? '—';
    }
}
