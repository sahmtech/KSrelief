<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class PatientEligibilityStatusPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'patient_status';
    }
}
