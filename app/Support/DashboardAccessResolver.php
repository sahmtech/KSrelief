<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class DashboardAccessResolver
{
    /** @var list<string> */
    public const ACCESS_PERMISSIONS = [
        'dashboard.view',
        'campaign.view',
        'patient.view',
        'member.view',
        'attendance.view',
        'transportation.view',
        'activity.view',
        'report.view',
        'stage.view',
        'medical_record.view',
    ];

    /** @var array<string, string> */
    public const MODULE_HOME_ROUTES = [
        'dashboard.view' => 'dashboard',
        'campaign.view' => 'campaigns.index',
        'patient.view' => 'patients.index',
        'member.view' => 'medical-staff.members.index',
        'attendance.view' => 'operations.attendance.index',
        'transportation.view' => 'operations.transportation.index',
        'activity.view' => 'operations.activities.index',
        'report.view' => 'reports.campaigns.index',
    ];

    public function canAccess(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        foreach (self::ACCESS_PERMISSIONS as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasExecutiveView(User $user): bool
    {
        return $user->can('dashboard.view');
    }

    public function defaultRoute(User $user): string
    {
        if ($this->canAccess($user)) {
            return route('dashboard');
        }

        foreach (self::MODULE_HOME_ROUTES as $permission => $routeName) {
            if ($user->can($permission)) {
                return route($routeName);
            }
        }

        return route('login');
    }

    /**
     * @return array{title: string, subtitle: string}
     */
    public function presentation(User $user): array
    {
        $subtitle = match (true) {
            $user->hasRole('doctor') => __('dashboard.subtitles.doctor'),
            $user->hasRole('attendance_officer') => __('dashboard.subtitles.attendance_officer'),
            $user->hasRole('reports_officer') => __('dashboard.subtitles.reports_officer'),
            $this->hasExecutiveView($user) => __('dashboard.subtitle'),
            $user->can('patient.view') || $user->can('stage.view') => __('dashboard.subtitles.clinical'),
            $user->can('attendance.view') => __('dashboard.subtitles.attendance_officer'),
            default => __('dashboard.subtitles.operational'),
        };

        return [
            'title' => __('dashboard.title'),
            'subtitle' => $subtitle,
        ];
    }

    public function registerGate(): void
    {
        Gate::define('accessDashboard', fn (User $user): bool => $this->canAccess($user));
    }

}
