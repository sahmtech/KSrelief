<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostOperationFormDefault extends Model
{
    protected $fillable = [
        'user_id',
        'defaults_json',
    ];

    protected function casts(): array
    {
        return [
            'defaults_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
