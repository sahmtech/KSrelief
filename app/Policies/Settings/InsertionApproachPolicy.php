<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class InsertionApproachPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'insertion_approach';
    }
}
