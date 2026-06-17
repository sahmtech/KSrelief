<?php

namespace App\Policies\Settings;

use App\Policies\Concerns\SettingPolicy;

class AttendanceStatusPolicy extends SettingPolicy
{
    protected function permissionPrefix(): string
    {
        return 'attendance_status';
    }
}
