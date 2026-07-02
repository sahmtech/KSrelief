<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignOperationDefault extends Model
{
    protected $fillable = [
        'campaign_id',
        'implant_company_id',
        'defaults_json',
    ];

    protected function casts(): array
    {
        return [
            'defaults_json' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function implantCompany(): BelongsTo
    {
        return $this->belongsTo(ImplantCompany::class);
    }
}
