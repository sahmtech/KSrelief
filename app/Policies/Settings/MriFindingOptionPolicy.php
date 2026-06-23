<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class MriFindingOptionPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'mri_finding_option';
    }
}
