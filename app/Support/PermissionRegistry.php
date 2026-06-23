<?php

namespace App\Support;

/**
 * Central registry of all system permissions.
 */
final class PermissionRegistry
{
    public const GUARD = 'web';

    /** @var array<string, list<string>> */
    public const GROUPS = [
        'campaigns' => [
            'campaign.view',
            'campaign.create',
            'campaign.update',
            'campaign.delete',
            'campaign.close',
        ],
        'patients' => [
            'patient.view',
            'patient.create',
            'patient.update',
            'patient.delete',
            'patient.import_excel',
            'patient.import_approve',
            'patient.import_history',
            'patient.export',
        ],
        'medical_records' => [
            'medical_record.view',
            'medical_record.create',
            'medical_record.update',
            'medical_record.delete',
        ],
        'medical_stages' => [
            'stage.view',
            'stage.change',
            'stage.history.view',
        ],
        'members' => [
            'member.view',
            'member.create',
            'member.update',
            'member.delete',
            'member.assign_campaign',
            'member.import_excel',
        ],
        'attendance' => [
            'attendance.view',
            'attendance.create',
            'attendance.update',
            'attendance.delete',
            'attendance.export',
        ],
        'transportation' => [
            'transportation.view',
            'transportation.create',
            'transportation.update',
            'transportation.delete',
            'transportation.manage_passengers',
            'transportation.change_status',
        ],
        'activities' => [
            'activity.view',
            'activity.create',
            'activity.update',
            'activity.delete',
            'activity.manage_participants',
            'activity.change_status',
        ],
        'reports' => [
            'report.view',
            'report.export_excel',
            'report.export_pdf',
        ],
        'settings' => [
            'settings.view',
            'settings.update',
        ],
        'countries' => [
            'country.view',
            'country.create',
            'country.update',
            'country.delete',
        ],
        'cities' => [
            'city.view',
            'city.create',
            'city.update',
            'city.delete',
        ],
        'specialties' => [
            'specialty.view',
            'specialty.create',
            'specialty.update',
            'specialty.delete',
        ],
        'member_roles' => [
            'member_role.view',
            'member_role.create',
            'member_role.update',
            'member_role.delete',
        ],
        'patient_statuses' => [
            'patient_status.view',
            'patient_status.create',
            'patient_status.update',
            'patient_status.delete',
        ],
        'stage_settings' => [
            'stage_settings.view',
            'stage_settings.create',
            'stage_settings.update',
            'stage_settings.delete',
        ],
        'activity_types' => [
            'activity_type.view',
            'activity_type.create',
            'activity_type.update',
            'activity_type.delete',
        ],
        'transport_locations' => [
            'transport_location.view',
            'transport_location.create',
            'transport_location.update',
            'transport_location.delete',
        ],
        'attendance_statuses' => [
            'attendance_status.view',
            'attendance_status.create',
            'attendance_status.update',
            'attendance_status.delete',
        ],
        'campaign_statuses' => [
            'campaign_status.view',
            'campaign_status.create',
            'campaign_status.update',
            'campaign_status.delete',
        ],
        'implant_companies' => [
            'implant_company.view',
            'implant_company.create',
            'implant_company.update',
            'implant_company.delete',
        ],
        'insertion_approaches' => [
            'insertion_approach.view',
            'insertion_approach.create',
            'insertion_approach.update',
            'insertion_approach.delete',
        ],
        'ct_finding_options' => [
            'ct_finding_option.view',
            'ct_finding_option.create',
            'ct_finding_option.update',
            'ct_finding_option.delete',
        ],
        'mri_finding_options' => [
            'mri_finding_option.view',
            'mri_finding_option.create',
            'mri_finding_option.update',
            'mri_finding_option.delete',
        ],
        'expectation_post_ci_options' => [
            'expectation_post_ci_option.view',
            'expectation_post_ci_option.create',
            'expectation_post_ci_option.update',
            'expectation_post_ci_option.delete',
        ],
        'users' => [
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
        ],
        'roles' => [
            'role.view',
            'role.create',
            'role.update',
            'role.delete',
        ],
        'dashboard' => [
            'dashboard.view',
            'campaign_dashboard.view',
        ],
    ];

    /** @var array<string, string|list<string>> */
    public const ROLE_PERMISSIONS = [
        'super_admin' => '*',
        'campaign_manager' => [
            'dashboard',
            'campaigns',
            'patients',
            'medical_records',
            'medical_stages',
            'members',
            'attendance',
            'transportation',
            'activities',
            'reports',
            'settings',
            'countries',
            'cities',
            'specialties',
            'member_roles',
            'patient_statuses',
            'stage_settings',
            'activity_types',
            'transport_locations',
            'attendance_statuses',
            'campaign_statuses',
            'implant_companies',
            'insertion_approaches',
            'ct_finding_options',
            'mri_finding_options',
            'expectation_post_ci_options',
        ],
        'campaign_coordinator' => [
            'dashboard.view',
            'campaign_dashboard.view',
            'campaign.view',
            'patients',
            'medical_records',
            'medical_stages',
            'attendance',
            'transportation',
            'activities',
            'country.view',
            'city.view',
            'specialty.view',
            'campaign_status.view',
        ],
        'doctor' => [
            'patient.view',
            'medical_record.view',
            'stage.view',
        ],
        'attendance_officer' => [
            'attendance',
            'attendance_statuses',
        ],
        'reports_officer' => [
            'dashboard.view',
            'reports',
        ],
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return collect(self::GROUPS)->flatten()->unique()->values()->all();
    }

    /** @return list<string> */
    public static function forGroup(string $group): array
    {
        return self::GROUPS[$group] ?? [];
    }

    /** @return list<string> */
    public static function forRole(string $role): array
    {
        $definition = self::ROLE_PERMISSIONS[$role] ?? [];

        if ($definition === '*') {
            return self::all();
        }

        return collect($definition)
            ->flatMap(function (string $entry): array {
                if (str_contains($entry, '.')) {
                    return [$entry];
                }

                return self::forGroup($entry);
            })
            ->unique()
            ->values()
            ->all();
    }
}
