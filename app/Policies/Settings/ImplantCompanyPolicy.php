<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class ImplantCompanyPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'implant_company';
    }
}
