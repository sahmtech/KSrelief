<?php

namespace App\Enums;

enum SystemRole: string
{
    case SuperAdmin = 'super_admin';
    case CampaignManager = 'campaign_manager';
    case CampaignCoordinator = 'campaign_coordinator';
    case Doctor = 'doctor';
    case AttendanceOfficer = 'attendance_officer';
    case ReportsOfficer = 'reports_officer';

  /**
   * @return list<string>
   */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return __('roles.system.'.$this->value);
    }
}
