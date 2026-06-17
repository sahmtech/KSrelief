<?php

namespace App\Models;

use App\Enums\SettingStatus;
use App\Models\Concerns\HasAuditUsers;
use App\Models\Concerns\HasSettingSearch;
use App\Models\Concerns\HasSettingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignStatusRecord extends Model
{
    use HasAuditUsers;
    use HasSettingSearch;
    use HasSettingStatus;
    use SoftDeletes;

    protected $table = 'campaign_statuses';

    /** @var list<string> */
    protected array $searchableColumns = [
        'name',
        'code',
    ];

    protected $fillable = [
        'name',
        'code',
        'color',
        'is_default',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'status' => SettingStatus::class,
        ];
    }

    public function label(): string
    {
        return __('campaigns.status.'.$this->code);
    }

    public function badgeClass(): string
    {
        return match ($this->code) {
            'draft' => 'badge-status--inactive',
            'planned' => 'badge-status--pending',
            'active' => 'badge-status--active',
            'completed' => 'badge-status--completed',
            'cancelled' => 'badge-status--cancelled',
            default => 'badge-status--inactive',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this->code, ['completed', 'cancelled'], true);
    }

    /**
     * @param  Builder<CampaignStatusRecord>  $query
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'campaign_status_id');
    }
}
