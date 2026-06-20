<?php

return [

    'name' => env('APP_NAME', 'KSrelief'),

    'locales' => [
        'en' => 'English',
        'ar' => 'العربية',
    ],

  /*
    |--------------------------------------------------------------------------
    | UI Feature Toggles (temporary)
    |--------------------------------------------------------------------------
    */
    'show_locale_switcher' => false,
    'show_notifications' => false,
    'show_navbar_search' => false,
    'show_reports' => false,
    'show_patient_transportation_tab' => false,
    'show_campaign_daily_schedule_tab' => false,

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu
    |--------------------------------------------------------------------------
    | Each item may define a `permission` key used for @can / menu filtering.
    | Dashboard is visible to all authenticated users.
    */
    'menu' => [
        [
            'key' => 'dashboard',
            'icon' => 'ti ti-dashboard',
            'route' => 'dashboard',
            'gate' => 'accessDashboard',
        ],
        [
            'key' => 'campaign_management',
            'icon' => 'ti ti-flag',
            'children' => [
                ['key' => 'campaigns', 'route' => 'campaigns.index', 'permission' => 'campaign.view'],
            ],
        ],
        [
            'key' => 'medical_staff',
            'icon' => 'ti ti-stethoscope',
            'children' => [
                ['key' => 'members', 'route' => 'medical-staff.members.index', 'permission' => 'member.view'],
            ],
        ],
        [
            'key' => 'patients',
            'icon' => 'ti ti-user-heart',
            'children' => [
                ['key' => 'patients_list', 'route' => 'patients.index', 'permission' => 'patient.view'],
                ['key' => 'patient_imports', 'route' => 'patients.import.index', 'permission' => 'patient.import_history'],
            ],
        ],
        [
            'key' => 'operations',
            'icon' => 'ti ti-activity',
            'children' => [
                ['key' => 'attendance', 'route' => 'operations.attendance.index', 'permission' => 'attendance.view'],
                ['key' => 'transportation', 'route' => 'operations.transportation.index', 'permission' => 'transportation.view'],
                ['key' => 'activities', 'route' => 'operations.activities.index', 'permission' => 'activity.view'],
                ['key' => 'activities_calendar', 'route' => 'operations.activities.calendar', 'permission' => 'activity.view'],
            ],
        ],
        [
            'key' => 'reports',
            'icon' => 'ti ti-report-analytics',
            'children' => [
                ['key' => 'campaign_reports', 'route' => 'reports.campaigns.index', 'permission' => 'report.view'],
                ['key' => 'patient_reports', 'route' => 'reports.patients.index', 'permission' => 'report.view'],
                ['key' => 'attendance_reports', 'route' => 'reports.attendance.index', 'permission' => 'report.view'],
            ],
        ],
        [
            'key' => 'administration',
            'icon' => 'ti ti-settings',
            'children' => [
                ['key' => 'users', 'route' => 'administration.users.index', 'permission' => 'user.view'],
                ['key' => 'roles', 'route' => 'administration.roles.index', 'permission' => 'role.view'],
            ],
        ],
        [
            'key' => 'settings',
            'icon' => 'ti ti-adjustments',
            'children' => [
                ['key' => 'settings_dashboard', 'route' => 'settings.dashboard', 'permission' => 'settings.view'],
                ['key' => 'countries', 'route' => 'settings.countries.index', 'permission' => 'country.view'],
                ['key' => 'cities', 'route' => 'settings.cities.index', 'permission' => 'city.view'],
                ['key' => 'specialties', 'route' => 'settings.specialties.index', 'permission' => 'specialty.view'],
                ['key' => 'member_roles', 'route' => 'settings.member-roles.index', 'permission' => 'member_role.view'],
                ['key' => 'patient_eligibility_statuses', 'route' => 'settings.patient-eligibility-statuses.index', 'permission' => 'patient_status.view'],
                ['key' => 'patient_stages', 'route' => 'settings.patient-stages.index', 'permission' => 'stage_settings.view'],
                ['key' => 'activity_types', 'route' => 'settings.activity-types.index', 'permission' => 'activity_type.view'],
                ['key' => 'transportation_locations', 'route' => 'settings.transportation-locations.index', 'permission' => 'transport_location.view'],
                ['key' => 'attendance_statuses', 'route' => 'settings.attendance-statuses.index', 'permission' => 'attendance_status.view'],
                ['key' => 'campaign_statuses', 'route' => 'settings.campaign-statuses.index', 'permission' => 'campaign_status.view'],
            ],
        ],
    ],

];
