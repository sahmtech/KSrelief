<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationDispatch extends Model
{
    protected $fillable = [
        'sent_by',
        'type',
        'title',
        'body',
        'data',
        'target_users',
        'tokens_attempted',
        'tokens_succeeded',
        'tokens_failed',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
