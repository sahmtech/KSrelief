<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignMember extends Model
{
    protected $table = 'campaign_member';

    protected $fillable = [
        'campaign_id',
        'member_id',
        'assigned_role',
        'assigned_from',
        'assigned_to',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_from' => 'date',
            'assigned_to' => 'date',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        if ($this->assigned_to === null) {
            return true;
        }

        return $this->assigned_to->isFuture() || $this->assigned_to->isToday();
    }

    public function isActiveOn(\Illuminate\Support\Carbon $date): bool
    {
        $day = $date->copy()->startOfDay();

        if ($this->assigned_from && $this->assigned_from->gt($day)) {
            return false;
        }

        if ($this->assigned_to && $this->assigned_to->lt($day)) {
            return false;
        }

        return true;
    }
}
