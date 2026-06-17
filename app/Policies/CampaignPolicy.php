<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('campaign.view');
    }

    public function view(User $user, Campaign $campaign): bool
    {
        return $user->can('campaign.view');
    }

    public function create(User $user): bool
    {
        return $user->can('campaign.create');
    }

    public function update(User $user, Campaign $campaign): bool
    {
        if ($campaign->isTerminalStatus()) {
            return $user->can('campaign.close') || $user->can('campaign.update');
        }

        return $user->can('campaign.update');
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->can('campaign.delete');
    }

    public function changeStatus(User $user, Campaign $campaign): bool
    {
        return $user->can('campaign.close') || $user->can('campaign.update');
    }

    public function close(User $user, Campaign $campaign): bool
    {
        return $user->can('campaign.close');
    }
}
