<?php

namespace App\Models;

use App\Enums\SettingStatus;
use App\Models\Concerns\HasAuditUsers;
use App\Models\Concerns\HasSettingSearch;
use App\Models\Concerns\HasSettingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportationLocation extends Model
{
    use HasAuditUsers;
    use HasSettingSearch;
    use HasSettingStatus;
    use SoftDeletes;

    /** @var list<string> */
    protected array $searchableColumns = [
        'name',
        'type',
        'description',
    ];

    protected $fillable = [
        'name',
        'type',
        'description',
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
}
