<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use App\Support\DashboardAccessResolver;
use App\Support\DashboardFilter;
use App\Support\DashboardScopeResolver;

class DashboardService
{
    public function __construct(
        private readonly DashboardStatisticsService $dashboardStatisticsService,
        private readonly DashboardWidgetService $dashboardWidgetService,
        private readonly DashboardScopeResolver $dashboardScopeResolver,
        private readonly DashboardAccessResolver $dashboardAccessResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getExecutiveDashboard(User $user, DashboardFilter $filter): array
    {
        $filter = $this->applyScope($user, $filter);
        $stats = $this->dashboardStatisticsService->getExecutiveData($user, $filter);
        $visibleSections = $this->dashboardWidgetService->visibleSections($user);
        $overviewCards = $this->dashboardWidgetService->buildOverviewCards($user, $stats['overview'], $stats);
        $charts = $this->dashboardWidgetService->buildCharts($filter, $user);
        $chartRows = $this->dashboardWidgetService->buildChartRows($charts);
        $quickActions = $this->dashboardWidgetService->buildQuickActions($user);
        $recentFeed = $this->dashboardStatisticsService->getRecentFeed($filter, $user);

        return [
            'filter' => $filter,
            'filterOptions' => $this->dashboardStatisticsService->getFilterOptions($filter),
            'presentation' => $this->dashboardAccessResolver->presentation($user),
            'overviewCards' => $overviewCards,
            'stats' => $stats,
            'charts' => $charts,
            'chartRows' => $chartRows,
            'recentFeed' => $recentFeed,
            'upcoming' => $this->dashboardStatisticsService->getUpcomingEvents($filter, $user),
            'quickActions' => $quickActions,
            'visibleSections' => $visibleSections,
            'isEmpty' => $this->dashboardWidgetService->isEmptyDashboard(
                $user,
                $visibleSections,
                $overviewCards,
                $quickActions,
                $chartRows,
                $recentFeed,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getCampaignDashboard(User $user, Campaign $campaign): array
    {
        abort_unless($this->dashboardScopeResolver->canAccessCampaign($user, $campaign->id), 403);

        $filter = (new DashboardFilter(campaignId: $campaign->id))->forCampaign($campaign->id);
        $stats = $this->dashboardStatisticsService->getCampaignDashboardData($campaign);
        $visibleSections = $this->dashboardWidgetService->visibleSections($user);
        $overviewCards = $this->dashboardWidgetService->buildOverviewCards($user, $stats['overview'], $stats);
        $charts = $this->dashboardWidgetService->buildCharts($filter, $user);
        $chartRows = $this->dashboardWidgetService->buildChartRows($charts);
        $quickActions = $this->dashboardWidgetService->buildQuickActions($user);
        $recentFeed = $this->dashboardStatisticsService->getRecentFeed($filter, $user);

        return [
            'campaign' => $campaign,
            'filter' => $filter,
            'presentation' => $this->dashboardAccessResolver->presentation($user),
            'stats' => $stats,
            'overviewCards' => $overviewCards,
            'charts' => $charts,
            'chartRows' => $chartRows,
            'recentFeed' => $recentFeed,
            'upcoming' => $this->dashboardStatisticsService->getUpcomingEvents($filter, $user),
            'quickActions' => $quickActions,
            'visibleSections' => $visibleSections,
            'isEmpty' => $this->dashboardWidgetService->isEmptyDashboard(
                $user,
                $visibleSections,
                $overviewCards,
                $quickActions,
                $chartRows,
                $recentFeed,
            ),
        ];
    }

    /** @return array<string, int> */
    public function getOverviewKPIs(DashboardFilter $filter): array
    {
        return $this->dashboardStatisticsService->getOverviewKPIs($filter);
    }

    /** @return array<string, int> */
    public function getPatientKPIs(DashboardFilter $filter): array
    {
        return $this->dashboardStatisticsService->getPatientKPIs($filter);
    }

    /** @return array<string, int|float> */
    public function getAttendanceKPIs(DashboardFilter $filter): array
    {
        return $this->dashboardStatisticsService->getAttendanceKPIs($filter);
    }

    /** @return array<string, int> */
    public function getTransportationKPIs(DashboardFilter $filter): array
    {
        return $this->dashboardStatisticsService->getTransportationKPIs($filter);
    }

    /** @return array<string, int|float> */
    public function getActivityKPIs(DashboardFilter $filter): array
    {
        return $this->dashboardStatisticsService->getActivityKPIs($filter);
    }

    /** @return array<string, int> */
    public function getCampaignKPIs(DashboardFilter $filter): array
    {
        return $this->dashboardStatisticsService->getCampaignKPIs($filter);
    }

    private function applyScope(User $user, DashboardFilter $filter): DashboardFilter
    {
        return new DashboardFilter(
            campaignId: $filter->campaignId,
            dateFrom: $filter->dateFrom,
            dateTo: $filter->dateTo,
            specialtyId: $filter->specialtyId,
            countryId: $filter->countryId,
            cityId: $filter->cityId,
            scopedCampaignIds: $this->dashboardScopeResolver->scopedCampaignIds($user),
        );
    }
}
