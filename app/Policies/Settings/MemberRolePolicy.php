<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class MemberRolePolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'member_role';
    }
}
