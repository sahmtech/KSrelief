<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class SpecialtyPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'specialty';
    }
}
