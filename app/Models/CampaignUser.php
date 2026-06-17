<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot model for future campaign-level access control.
 *
 * This table links users to campaigns with a specific role, enabling:
 * - Campaign Managers to access only assigned campaigns
 * - Campaign Coordinators to operate within assigned campaigns
 *
 * NOT YET ENFORCED — global RBAC is active; campaign scoping will be
 * implemented when the Campaign module is built.
 */
class CampaignUser extends Model
{
    protected $table = 'campaign_user';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'role_id',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
