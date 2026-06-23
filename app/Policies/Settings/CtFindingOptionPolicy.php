<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class CtFindingOptionPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'ct_finding_option';
    }
}
