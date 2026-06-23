<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class ExpectationPostCiOptionPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'expectation_post_ci_option';
    }
}
