<?php

namespace App\Models;

use App\Enums\SettingStatus;
use App\Models\Concerns\HasAuditUsers;
use App\Models\Concerns\HasSettingSearch;
use App\Models\Concerns\HasSettingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasAuditUsers;
    use HasSettingSearch;
    use HasSettingStatus;
    use SoftDeletes;

    /** @var list<string> */
    protected array $searchableColumns = [
        'name',
        'name_ar',
        'code',
        'iso2',
        'iso3',
    ];

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'iso2',
        'iso3',
        'phone_code',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => SettingStatus::class,
        ];
    }

    public function localizedName(): string
    {
        if (app()->getLocale() === 'ar' && filled($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name;
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
