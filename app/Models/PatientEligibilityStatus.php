<?php

namespace App\Models;

use App\Enums\SettingStatus;
use App\Models\Concerns\HasAuditUsers;
use App\Models\Concerns\HasSettingSearch;
use App\Models\Concerns\HasSettingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientEligibilityStatus extends Model
{
    use HasAuditUsers;
    use HasSettingSearch;
    use HasSettingStatus;
    use SoftDeletes;

    /** @var list<string> */
    protected array $searchableColumns = [
        'name',
        'code',
    ];

    protected $fillable = [
        'name',
        'code',
        'color',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => SettingStatus::class,
        ];
    }

    /**
     * @param  Builder<PatientEligibilityStatus>  $query
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
