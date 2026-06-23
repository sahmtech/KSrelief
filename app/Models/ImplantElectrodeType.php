<?php

namespace App\Models;

use App\Enums\SettingStatus;
use App\Models\Concerns\HasSettingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImplantElectrodeType extends Model
{
    use HasSettingStatus;
    use SoftDeletes;

    protected $fillable = [
        'implant_company_id',
        'name',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => SettingStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(ImplantCompany::class, 'implant_company_id');
    }
}
