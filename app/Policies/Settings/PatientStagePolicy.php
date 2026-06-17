<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class PatientStagePolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'stage_settings';
    }
}
