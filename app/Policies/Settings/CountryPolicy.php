<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class CountryPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'country';
    }
}
