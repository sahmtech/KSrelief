<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class TransportationLocationPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'transport_location';
    }
}
