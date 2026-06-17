<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use App\Support\DashboardAccessResolver;

class DashboardPolicy
{
    public function __construct(
        private readonly DashboardAccessResolver $dashboardAccessResolver,
    ) {}

    public function viewDashboard(User $user): bool
    {
        return $this->dashboardAccessResolver->canAccess($user);
    }

    public function viewCampaignDashboard(User $user, Campaign $campaign): bool
    {
        if (! $user->can('campaign_dashboard.view')) {
            return false;
        }

        return app(\App\Support\DashboardScopeResolver::class)->canAccessCampaign($user, $campaign->id);
    }
}
