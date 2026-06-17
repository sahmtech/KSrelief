<?php

namespace App\Support;

use App\Models\CampaignUser;
use App\Models\Member;
use App\Models\User;

final class DashboardScopeResolver
{
    /**
     * Resolve campaign IDs the user may view on dashboards.
     *
     * @return list<int>|null Null = unrestricted (all campaigns).
     */
    public function scopedCampaignIds(User $user): ?array
    {
        if ($user->hasRole('super_admin')) {
            return null;
        }

        $assigned = CampaignUser::query()
            ->where('user_id', $user->id)
            ->pluck('campaign_id')
            ->all();

        if ($assigned !== []) {
            return array_values(array_unique(array_map('intval', $assigned)));
        }

        $memberCampaignIds = Member::query()
            ->where('user_id', $user->id)
            ->whereHas('campaignAssignments')
            ->with('campaignAssignments:campaign_id,member_id')
            ->get()
            ->flatMap(fn (Member $member) => $member->campaignAssignments->pluck('campaign_id'))
            ->unique()
            ->values()
            ->all();

        if ($memberCampaignIds !== []) {
            return array_values(array_unique(array_map('intval', $memberCampaignIds)));
        }

        if ($user->can('campaign.view')) {
            return null;
        }

        return null;
    }

    public function canAccessCampaign(User $user, int $campaignId): bool
    {
        $scoped = $this->scopedCampaignIds($user);

        if ($scoped === null) {
            return true;
        }

        return in_array($campaignId, $scoped, true);
    }
}
