<?php

namespace App\Services\Push;

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\PatientAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class PushRecipientResolver
{
    public function __construct(
        private readonly PatientAccessService $campaignAccess,
    ) {}

    /**
     * Clinical mobile users for a campaign (doctors / coordinators by default).
     *
     * @return Collection<int, User>
     */
    public function forCampaign(int $campaignId, ?int $excludeUserId = null): Collection
    {
        $roles = config('push_notifications.recipient_roles', ['doctor']);

        return User::query()
            ->where('status', UserStatus::Active)
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', $roles))
            ->where(function (Builder $q) use ($campaignId): void {
                $q->whereHas('campaignAssignments', fn (Builder $cq) => $cq->where('campaign_id', $campaignId))
                    ->orWhereHas('member.campaignAssignments', fn (Builder $mq) => $mq->where('campaign_id', $campaignId));
            })
            ->when($excludeUserId, fn (Builder $q) => $q->where('id', '!=', $excludeUserId))
            ->with('devicePushTokens')
            ->get();
    }

    /**
     * @param  list<int>  $userIds
     * @return Collection<int, User>
     */
    public function forUserIds(array $userIds): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        $roles = config('push_notifications.recipient_roles', ['doctor']);

        return User::query()
            ->whereIn('id', $userIds)
            ->where('status', UserStatus::Active)
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', $roles))
            ->with('devicePushTokens')
            ->get();
    }

    /**
     * All active users with configured clinical roles (optionally scoped by campaign).
     *
     * @return Collection<int, User>
     */
    public function allClinicalUsers(?int $campaignId = null): Collection
    {
        $roles = config('push_notifications.recipient_roles', ['doctor']);

        $query = User::query()
            ->where('status', UserStatus::Active)
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', $roles));

        if ($campaignId !== null) {
            $query->where(function (Builder $q) use ($campaignId): void {
                $q->whereHas('campaignAssignments', fn (Builder $cq) => $cq->where('campaign_id', $campaignId))
                    ->orWhereHas('member.campaignAssignments', fn (Builder $mq) => $mq->where('campaign_id', $campaignId));
            });
        } else {
            // Users without any campaign assignment still get broadcast if they have tokens (rare).
        }

        return $query->with('devicePushTokens')->get();
    }

    /**
     * Admins allowed to send broadcast pushes.
     */
    public function canBroadcast(User $user): bool
    {
        return $user->can('push.broadcast');
    }

    /**
     * Filter user IDs to those the sender may target (campaign scope for non-global roles).
     *
     * @param  list<int>  $userIds
     * @return list<int>
     */
    public function filterUserIdsForSender(User $sender, array $userIds, ?int $campaignId = null): array
    {
        if ($userIds === []) {
            return [];
        }

        $allowedCampaigns = $this->campaignAccess->allowedCampaignIds($sender);

        $users = User::query()
            ->whereIn('id', $userIds)
            ->where('status', UserStatus::Active)
            ->get(['id']);

        if ($allowedCampaigns === null) {
            return $users->pluck('id')->all();
        }

        if ($campaignId !== null && ! in_array($campaignId, $allowedCampaigns, true)) {
            return [];
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->where(function (Builder $q) use ($allowedCampaigns): void {
                $q->whereHas('campaignAssignments', fn (Builder $cq) => $cq->whereIn('campaign_id', $allowedCampaigns))
                    ->orWhereHas('member.campaignAssignments', fn (Builder $mq) => $mq->whereIn('campaign_id', $allowedCampaigns));
            })
            ->pluck('id')
            ->all();
    }
}
