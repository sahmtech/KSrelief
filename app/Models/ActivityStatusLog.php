<?php

namespace App\Models;

use App\Enums\ActivityLogEventType;
use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'activity_id',
        'event_type',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => ActivityLogEventType::class,
            'old_status' => ActivityStatus::class,
            'new_status' => ActivityStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
