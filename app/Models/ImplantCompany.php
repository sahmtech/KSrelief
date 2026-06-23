<?php

namespace App\Models;

use App\Enums\SettingStatus;
use App\Models\Concerns\HasAuditUsers;
use App\Models\Concerns\HasSettingSearch;
use App\Models\Concerns\HasSettingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImplantCompany extends Model
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
            'status' => SettingStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function electrodeTypes(): HasMany
    {
        return $this->hasMany(ImplantElectrodeType::class)->orderBy('sort_order')->orderBy('name');
    }

    public function activeElectrodeTypes(): HasMany
    {
        return $this->electrodeTypes()->where('status', SettingStatus::Active->value);
    }
}
