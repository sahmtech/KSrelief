<?php

namespace App\Models\Concerns;

use App\Enums\SettingStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasSettingStatus
{
    /**
     * @param  Builder<static>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SettingStatus::Active->value);
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (! filled($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function isActive(): bool
    {
        $status = $this->getAttribute('status');

        if ($status instanceof SettingStatus) {
            return $status === SettingStatus::Active;
        }

        return $status === SettingStatus::Active->value;
    }
}
