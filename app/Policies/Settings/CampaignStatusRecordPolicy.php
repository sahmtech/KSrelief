<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class CampaignStatusRecordPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'campaign_status';
    }
}
