<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class ActivityTypePolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'activity_type';
    }
}
