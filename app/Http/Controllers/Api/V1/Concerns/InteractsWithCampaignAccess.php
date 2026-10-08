<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Services\PatientAccessService;

trait InteractsWithCampaignAccess
{
    protected function assertCampaignAccessible(int $campaignId): void
    {
        $user = request()->user();

        if (! $user) {
            abort(401);
        }

        app(PatientAccessService::class)->assertCanAccessCampaign($user, $campaignId);
    }

    protected function assertModelCampaignAccessible(object $model): void
    {
        if (! isset($model->campaign_id)) {
            return;
        }

        $this->assertCampaignAccessible((int) $model->campaign_id);
    }
}
