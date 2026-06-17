<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class CityPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'city';
    }
}
