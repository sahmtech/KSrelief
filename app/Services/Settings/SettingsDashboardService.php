<?php

namespace App\Services\Settings;

use App\Models\ActivityType;
use App\Models\AttendanceStatus;
use App\Models\CampaignStatusRecord;
use App\Models\City;
use App\Models\Country;
use App\Models\MemberRole;
use App\Models\PatientEligibilityStatus;
use App\Models\PatientStage;
use App\Models\Specialty;
use App\Models\TransportationLocation;
use Illuminate\Database\Eloquent\Model;

class SettingsDashboardService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function getCards(): array
    {
        return [
            $this->buildCard('countries', Country::class, 'settings.countries.index', 'country.view'),
            $this->buildCard('cities', City::class, 'settings.cities.index', 'city.view'),
            $this->buildCard('specialties', Specialty::class, 'settings.specialties.index', 'specialty.view'),
            $this->buildCard('member_roles', MemberRole::class, 'settings.member-roles.index', 'member_role.view'),
            $this->buildCard('patient_eligibility_statuses', PatientEligibilityStatus::class, 'settings.patient-eligibility-statuses.index', 'patient_status.view'),
            $this->buildCard('patient_stages', PatientStage::class, 'settings.patient-stages.index', 'stage_settings.view'),
            $this->buildCard('activity_types', ActivityType::class, 'settings.activity-types.index', 'activity_type.view'),
            $this->buildCard('transportation_locations', TransportationLocation::class, 'settings.transportation-locations.index', 'transport_location.view'),
            $this->buildCard('attendance_statuses', AttendanceStatus::class, 'settings.attendance-statuses.index', 'attendance_status.view'),
            $this->buildCard('campaign_statuses', CampaignStatusRecord::class, 'settings.campaign-statuses.index', 'campaign_status.view'),
        ];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array<string, mixed>
     */
    private function buildCard(string $key, string $modelClass, string $route, string $permission): array
    {
        return [
            'key' => $key,
            'label' => __("settings.dashboard.cards.{$key}"),
            'route' => $route,
            'permission' => $permission,
            'total' => $modelClass::query()->count(),
            'active' => $modelClass::query()->active()->count(),
        ];
    }
}
