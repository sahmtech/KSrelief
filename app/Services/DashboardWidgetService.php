<?php

namespace App\Services;

use App\Models\User;
use App\Support\DashboardAccessResolver;
use App\Support\DashboardFilter;
use Illuminate\Support\Collection;

class DashboardWidgetService
{
    /** @var array<string, string> */
    private const CHART_PERMISSIONS = [
        'patient_registrations' => 'patient.view',
        'patients_by_stage' => 'stage.view',
        'attendance_trend' => 'attendance.view',
        'activities_trend' => 'activity.view',
        'transportation_trend' => 'transportation.view',
        'campaign_trend' => 'campaign.view',
        'campaign_performance' => 'campaign.view',
    ];

    public function __construct(
        private readonly DashboardStatisticsService $dashboardStatisticsService,
        private readonly CampaignStatisticsService $campaignStatisticsService,
        private readonly DashboardAccessResolver $dashboardAccessResolver,
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function buildCharts(DashboardFilter $filter, User $user): array
    {
        $patientTrend = $this->dashboardStatisticsService->getPatientRegistrationTrend($filter);
        $stageStats = $this->dashboardStatisticsService->getWorkflowKPIs($filter)['patients_by_stage'];
        $stageWithData = $stageStats->filter(fn (array $row): bool => $row['count'] > 0)->values();
        $attendanceTrend = $this->dashboardStatisticsService->getAttendanceTrend($filter);
        $activityTrend = $this->dashboardStatisticsService->getActivityTrend($filter);
        $transportTrend = $this->dashboardStatisticsService->getTransportationTrend($filter);
        $campaignTrend = $this->campaignStatisticsService->getCampaignTrend(6, $filter);
        $performance = $this->campaignStatisticsService->getPerformanceComparison(8, $filter);

        $charts = [
            'campaign_trend' => $this->areaChart(
                __('dashboard.charts.campaign_trend'),
                array_column($campaignTrend, 'month'),
                array_column($campaignTrend, 'count'),
                __('dashboard.charts.campaigns'),
            ),
            'patients_by_stage' => $this->donutChart(
                __('dashboard.charts.patients_by_stage'),
                $stageWithData->isNotEmpty()
                    ? $stageWithData->pluck('stage.name')->all()
                    : [__('dashboard.charts.no_data')],
                $stageWithData->isNotEmpty()
                    ? $stageWithData->pluck('count')->all()
                    : [0],
                $stageWithData->pluck('stage.color')->filter()->all(),
            ),
            'attendance_trend' => $this->lineChart(
                __('dashboard.charts.attendance_trend'),
                array_column($attendanceTrend, 'month'),
                array_column($attendanceTrend, 'rate'),
                __('dashboard.charts.attendance_rate'),
                '%',
            ),
            'activities_trend' => $this->barChart(
                __('dashboard.charts.activities_trend'),
                array_column($activityTrend, 'month'),
                array_column($activityTrend, 'count'),
                __('dashboard.activities.title'),
            ),
            'transportation_trend' => $this->barChart(
                __('dashboard.charts.transportation_trend'),
                array_column($transportTrend, 'month'),
                array_column($transportTrend, 'count'),
                __('dashboard.transportation.title'),
            ),
            'patient_registrations' => $this->areaChart(
                __('dashboard.chart.title'),
                array_column($patientTrend, 'month'),
                array_column($patientTrend, 'count'),
                __('dashboard.chart.series_name'),
            ),
            'campaign_performance' => $this->groupedBarChart($performance),
        ];

        return collect($charts)
            ->filter(fn (array $chart, string $key): bool => $this->canViewChart($user, $key))
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $charts
     * @return list<list<array{chart: array<string, mixed>, class: string, height: int}>>
     */
    public function buildChartRows(array $charts): array
    {
        if ($charts === []) {
            return [];
        }

        $rows = [];
        $remaining = array_keys($charts);

        if (isset($charts['patient_registrations'], $charts['patients_by_stage'])) {
            $rows[] = [
                ['chart' => $charts['patient_registrations'], 'class' => 'col-lg-8', 'height' => 300],
                ['chart' => $charts['patients_by_stage'], 'class' => 'col-lg-4', 'height' => 300],
            ];
            $remaining = array_values(array_diff($remaining, ['patient_registrations', 'patients_by_stage']));
        } elseif (isset($charts['patient_registrations'])) {
            $rows[] = [
                ['chart' => $charts['patient_registrations'], 'class' => 'col-12', 'height' => 300],
            ];
            $remaining = array_values(array_diff($remaining, ['patient_registrations']));
        } elseif (isset($charts['patients_by_stage'])) {
            $rows[] = [
                ['chart' => $charts['patients_by_stage'], 'class' => 'col-12', 'height' => 300],
            ];
            $remaining = array_values(array_diff($remaining, ['patients_by_stage']));
        }

        $pairKeys = ['attendance_trend', 'activities_trend', 'transportation_trend', 'campaign_trend'];
        $paired = array_values(array_intersect($pairKeys, $remaining));

        foreach (array_chunk($paired, 2) as $chunk) {
            $row = [];
            foreach ($chunk as $key) {
                $row[] = ['chart' => $charts[$key], 'class' => count($chunk) === 1 ? 'col-12' : 'col-lg-6', 'height' => 280];
            }
            $rows[] = $row;
            $remaining = array_values(array_diff($remaining, $chunk));
        }

        foreach ($remaining as $key) {
            $rows[] = [[
                'chart' => $charts[$key],
                'class' => 'col-12',
                'height' => $key === 'campaign_performance' ? 340 : 300,
            ]];
        }

        return $rows;
    }

    /**
     * @return list<array{label: string, value: string, icon: string, variant: string}>
     */
    public function buildOverviewCards(User $user, array $overview, array $stats): array
    {
        $pool = [];

        if ($user->can('campaign.view')) {
            $pool[] = ['label' => __('dashboard.kpi.total_campaigns'), 'value' => (string) $overview['total_campaigns'], 'icon' => 'ti-flag', 'variant' => 'primary'];
            $pool[] = ['label' => __('dashboard.kpi.active_campaigns'), 'value' => (string) $overview['active_campaigns'], 'icon' => 'ti-flag-2', 'variant' => 'success'];
        }

        if ($user->can('patient.view')) {
            $patients = $stats['patients'];
            $pool[] = ['label' => __('dashboard.kpi.total_patients'), 'value' => number_format($patients['total']), 'icon' => 'ti-users', 'variant' => 'primary'];
            $pool[] = ['label' => __('dashboard.kpi.admitted'), 'value' => (string) $patients['admitted'], 'icon' => 'ti-bed', 'variant' => 'secondary'];
            $pool[] = ['label' => __('dashboard.kpi.accepted'), 'value' => (string) $patients['accepted'], 'icon' => 'ti-check', 'variant' => 'success'];
            $pool[] = ['label' => __('dashboard.kpi.completed_patients'), 'value' => (string) $patients['completed'], 'icon' => 'ti-circle-check', 'variant' => 'success'];
        }

        if ($user->can('stage.view')) {
            $workflow = $stats['workflow'];
            $pool[] = ['label' => __('dashboard.kpi.workflow_completion'), 'value' => $overview['workflow_completion_rate'].'%', 'icon' => 'ti-progress', 'variant' => 'warning'];
            $pool[] = ['label' => __('dashboard.workflow.waiting_operation'), 'value' => (string) $workflow['waiting_operation'], 'icon' => 'ti-stethoscope', 'variant' => 'primary'];
            $pool[] = ['label' => __('dashboard.workflow.waiting_admission'), 'value' => (string) $workflow['waiting_admission'], 'icon' => 'ti-door-enter', 'variant' => 'warning'];
        }

        if ($user->can('attendance.view')) {
            $attendance = $stats['attendance'];
            $pool[] = ['label' => __('dashboard.attendance.present_today'), 'value' => (string) $attendance['present_today'], 'icon' => 'ti-circle-check', 'variant' => 'success'];
            $pool[] = ['label' => __('dashboard.attendance.attendance_rate'), 'value' => $attendance['attendance_rate'].'%', 'icon' => 'ti-percentage', 'variant' => 'primary'];
        }

        if ($user->can('transportation.view')) {
            $transportation = $stats['transportation'];
            $pool[] = ['label' => __('dashboard.transportation.today_trips'), 'value' => (string) $transportation['today_trips'], 'icon' => 'ti-bus', 'variant' => 'primary'];
            $pool[] = ['label' => __('dashboard.transportation.upcoming_trips'), 'value' => (string) $transportation['upcoming_trips'], 'icon' => 'ti-clock', 'variant' => 'warning'];
        }

        if ($user->can('activity.view')) {
            $activities = $stats['activities'];
            $pool[] = ['label' => __('dashboard.activities.today'), 'value' => (string) $activities['today'], 'icon' => 'ti-activity', 'variant' => 'primary'];
            $pool[] = ['label' => __('dashboard.activities.upcoming'), 'value' => (string) $activities['upcoming'], 'icon' => 'ti-clock', 'variant' => 'warning'];
        }

        if ($user->can('member.view')) {
            $members = $stats['members'];
            $pool[] = ['label' => __('dashboard.members.total'), 'value' => (string) $members['total'], 'icon' => 'ti-users', 'variant' => 'primary'];
            $pool[] = ['label' => __('dashboard.members.doctors'), 'value' => (string) $members['doctors'], 'icon' => 'ti-stethoscope', 'variant' => 'success'];
        }

        return collect($pool)
            ->unique('label')
            ->take(4)
            ->values()
            ->all();
    }

    /**
     * @return list<array{label: string, route: string|null, icon: string, permission: string|null}>
     */
    public function buildQuickActions(User $user): array
    {
        $actions = [];

        if ($user->can('patient.view')) {
            $actions[] = ['label' => __('dashboard.actions.view_patients'), 'route' => route('patients.index'), 'icon' => 'ti-users', 'permission' => 'patient.view'];
        }

        if ($user->can('campaign.create')) {
            $actions[] = ['label' => __('dashboard.actions.create_campaign'), 'route' => route('campaigns.create'), 'icon' => 'ti-flag-plus', 'permission' => 'campaign.create'];
        }

        if ($user->can('patient.create')) {
            $actions[] = ['label' => __('dashboard.actions.add_patient'), 'route' => route('patients.create'), 'icon' => 'ti-user-plus', 'permission' => 'patient.create'];
        }

        if ($user->can('patient.import_excel')) {
            $actions[] = ['label' => __('dashboard.actions.import_patients'), 'route' => route('patients.import.index'), 'icon' => 'ti-file-import', 'permission' => 'patient.import_excel'];
        }

        if ($user->can('activity.create')) {
            $actions[] = ['label' => __('dashboard.actions.create_activity'), 'route' => route('operations.activities.create'), 'icon' => 'ti-activity', 'permission' => 'activity.create'];
        }

        if ($user->can('transportation.create')) {
            $actions[] = ['label' => __('dashboard.actions.create_trip'), 'route' => route('operations.transportation.create'), 'icon' => 'ti-bus', 'permission' => 'transportation.create'];
        }

        if ($user->can('attendance.create')) {
            $actions[] = ['label' => __('dashboard.actions.record_attendance'), 'route' => route('operations.attendance.quick'), 'icon' => 'ti-clipboard-check', 'permission' => 'attendance.create'];
        }

        return $actions;
    }

    /**
     * @return array<string, bool|string>
     */
    public function visibleSections(User $user): array
    {
        $hasExecutiveView = $this->dashboardAccessResolver->hasExecutiveView($user);

        return [
            'overview' => $this->hasAnyPermission($user, [
                'campaign.view',
                'patient.view',
                'stage.view',
                'attendance.view',
                'transportation.view',
                'activity.view',
                'member.view',
            ]),
            'campaigns' => $user->can('campaign.view'),
            'patients' => $user->can('patient.view'),
            'workflow' => $user->can('stage.view'),
            'members' => $user->can('member.view'),
            'attendance' => $user->can('attendance.view'),
            'transportation' => $user->can('transportation.view'),
            'activities' => $user->can('activity.view'),
            'imports' => $user->can('patient.import_history'),
            'charts' => $this->hasAnyChart($user),
            'quick_actions' => true,
            'recent_activity' => $this->hasRecentFeedAccess($user),
            'upcoming_events' => $this->hasUpcomingAccess($user),
            'filters' => $hasExecutiveView || $user->can('campaign.view'),
            'filters_dates_only' => ! $hasExecutiveView && ! $user->can('campaign.view') && $user->can('patient.view'),
            'audit_placeholder' => $hasExecutiveView,
        ];
    }

    public function isEmptyDashboard(User $user, array $visibleSections, array $overviewCards, array $quickActions, array $chartRows, Collection $recentFeed): bool
    {
        if ($overviewCards !== [] || $quickActions !== [] || $chartRows !== []) {
            return false;
        }

        if ($recentFeed->isNotEmpty()) {
            return false;
        }

        foreach (['campaigns', 'patients', 'workflow', 'members', 'attendance', 'transportation', 'activities', 'imports'] as $section) {
            if ($visibleSections[$section] ?? false) {
                return false;
            }
        }

        return true;
    }

    private function canViewChart(User $user, string $chartKey): bool
    {
        $permission = self::CHART_PERMISSIONS[$chartKey] ?? null;

        return $permission ? $user->can($permission) : false;
    }

    private function hasAnyChart(User $user): bool
    {
        foreach (array_keys(self::CHART_PERMISSIONS) as $chartKey) {
            if ($this->canViewChart($user, $chartKey)) {
                return true;
            }
        }

        return false;
    }

    private function hasRecentFeedAccess(User $user): bool
    {
        return $this->hasAnyPermission($user, [
            'patient.view',
            'stage.view',
            'attendance.view',
            'transportation.view',
            'activity.view',
        ]);
    }

    private function hasUpcomingAccess(User $user): bool
    {
        return $this->hasAnyPermission($user, [
            'patient.view',
            'stage.view',
            'activity.view',
            'transportation.view',
            'campaign.view',
        ]);
    }

    /** @param  list<string>  $permissions */
    private function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /** @param  list<string>  $categories
     * @param  list<int|float>  $data
     * @return array<string, mixed>
     */
    private function areaChart(string $title, array $categories, array $data, string $seriesName): array
    {
        return [
            'title' => $title,
            'config' => [
                'chart' => ['type' => 'area', 'height' => 300, 'toolbar' => ['show' => false], 'fontFamily' => 'inherit'],
                'series' => [['name' => $seriesName, 'data' => $data]],
                'colors' => ['#0F766E'],
                'fill' => ['type' => 'gradient', 'gradient' => ['shadeIntensity' => 1, 'opacityFrom' => 0.4, 'opacityTo' => 0.05]],
                'stroke' => ['curve' => 'smooth', 'width' => 2],
                'dataLabels' => ['enabled' => false],
                'grid' => ['borderColor' => '#E2E8F0', 'strokeDashArray' => 4],
                'xaxis' => ['categories' => $categories, 'labels' => ['style' => ['colors' => '#64748B']]],
                'yaxis' => ['labels' => ['style' => ['colors' => '#64748B']]],
            ],
        ];
    }

    /** @param  list<string>  $labels
     * @param  list<int>  $series
     * @param  list<string|null>  $colors
     * @return array<string, mixed>
     */
    private function donutChart(string $title, array $labels, array $series, array $colors): array
    {
        return [
            'title' => $title,
            'config' => [
                'chart' => ['type' => 'donut', 'height' => 300, 'fontFamily' => 'inherit'],
                'series' => $series,
                'labels' => $labels,
                'colors' => $colors ?: ['#0F766E', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6', '#64748B'],
                'legend' => ['position' => 'bottom'],
                'dataLabels' => ['enabled' => true],
            ],
        ];
    }

    /** @param  list<string>  $categories
     * @param  list<int|float>  $data
     * @return array<string, mixed>
     */
    private function lineChart(string $title, array $categories, array $data, string $seriesName, string $suffix = ''): array
    {
        return [
            'title' => $title,
            'config' => [
                'chart' => ['type' => 'line', 'height' => 280, 'toolbar' => ['show' => false], 'fontFamily' => 'inherit'],
                'series' => [['name' => $seriesName, 'data' => $data]],
                'colors' => ['#3B82F6'],
                'stroke' => ['curve' => 'smooth', 'width' => 3],
                'dataLabels' => ['enabled' => false],
                'grid' => ['borderColor' => '#E2E8F0', 'strokeDashArray' => 4],
                'xaxis' => ['categories' => $categories],
                'yaxis' => ['max' => 100, 'labels' => ['style' => ['colors' => '#64748B']]],
            ],
        ];
    }

    /** @param  list<string>  $categories
     * @param  list<int>  $data
     * @return array<string, mixed>
     */
    private function barChart(string $title, array $categories, array $data, string $seriesName): array
    {
        return [
            'title' => $title,
            'config' => [
                'chart' => ['type' => 'bar', 'height' => 280, 'toolbar' => ['show' => false], 'fontFamily' => 'inherit'],
                'series' => [['name' => $seriesName, 'data' => $data]],
                'colors' => ['#0F766E'],
                'plotOptions' => ['bar' => ['borderRadius' => 4, 'columnWidth' => '55%']],
                'dataLabels' => ['enabled' => false],
                'grid' => ['borderColor' => '#E2E8F0', 'strokeDashArray' => 4],
                'xaxis' => ['categories' => $categories],
            ],
        ];
    }

    /** @param  Collection<int, array<string, mixed>>  $performance
     * @return array<string, mixed>
     */
    private function groupedBarChart(Collection $performance): array
    {
        if ($performance->isEmpty()) {
            return [
                'title' => __('dashboard.charts.campaign_performance'),
                'config' => [
                    'chart' => ['type' => 'bar', 'height' => 320, 'toolbar' => ['show' => false], 'fontFamily' => 'inherit'],
                    'series' => [['name' => __('dashboard.charts.no_data'), 'data' => [0]]],
                    'xaxis' => ['categories' => ['—']],
                    'colors' => ['#CBD5E1'],
                ],
            ];
        }

        $categories = $performance->map(fn (array $row) => \Illuminate\Support\Str::limit($row['campaign']->name, 18))->all();

        return [
            'title' => __('dashboard.charts.campaign_performance'),
            'config' => [
                'chart' => ['type' => 'bar', 'height' => 320, 'stacked' => false, 'toolbar' => ['show' => false], 'fontFamily' => 'inherit'],
                'series' => [
                    ['name' => __('dashboard.charts.patients'), 'data' => $performance->pluck('patients')->all()],
                    ['name' => __('dashboard.activities.title'), 'data' => $performance->pluck('activities')->all()],
                    ['name' => __('dashboard.charts.trips'), 'data' => $performance->pluck('trips')->all()],
                    ['name' => __('dashboard.attendance.title'), 'data' => $performance->pluck('attendance')->all()],
                ],
                'colors' => ['#0F766E', '#3B82F6', '#F59E0B', '#8B5CF6'],
                'plotOptions' => ['bar' => ['horizontal' => false, 'columnWidth' => '60%', 'borderRadius' => 3]],
                'dataLabels' => ['enabled' => false],
                'xaxis' => ['categories' => $categories],
                'legend' => ['position' => 'top'],
            ],
        ];
    }
}
