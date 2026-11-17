<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BeneficiaryApplicationAttachment extends Model
{
    protected $fillable = [
        'beneficiary_application_id',
        'field_key',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryApplication::class, 'beneficiary_application_id');
    }

    public function disk(): string
    {
        return (string) config('beneficiary_registration.disk', 'local');
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk())->exists($this->path);
    }
}
