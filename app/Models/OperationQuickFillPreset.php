<?php

namespace App\Models;

use App\Enums\SettingStatus;
use App\Models\Concerns\HasAuditUsers;
use App\Models\Concerns\HasSettingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationQuickFillPreset extends Model
{
    use HasAuditUsers;
    use HasSettingStatus;

    protected $fillable = [
        'implant_company_id',
        'name',
        'preset_json',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'preset_json' => 'array',
            'status' => SettingStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function implantCompany(): BelongsTo
    {
        return $this->belongsTo(ImplantCompany::class);
    }
}
