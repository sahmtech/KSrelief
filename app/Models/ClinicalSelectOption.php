<?php

namespace App\Models;

use App\Enums\SettingStatus;
use App\Models\Concerns\HasAuditUsers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClinicalSelectOption extends Model
{
    use HasAuditUsers;
    use SoftDeletes;

    protected $fillable = [
        'category',
        'code',
        'name',
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
}
