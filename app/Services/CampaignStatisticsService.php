<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignStatusRecord;
use App\Support\DashboardFilter;
use Illuminate\Support\Collection;

class CampaignStatisticsService
{
    /**
     * @return array{
     *     total: int,
     *     active: int,
     *     completed: int,
     *     cancelled: int,
     *     upcoming: int,
     * }
     */
    public function getStats(?DashboardFilter $filter = null): array
    {
        $statusIds = CampaignStatusRecord::query()
            ->whereIn('code', ['active', 'completed', 'cancelled', 'draft', 'planned'])
            ->pluck('id', 'code');

        $today = now()->toDateString();
        $query = Campaign::query();

        if ($filter) {
            $ids = $filter->resolvedCampaignIds();

            if ($ids !== null) {
                $query->whereIn('id', $ids);
            }

            if ($filter->campaignId) {
                $query->where('id', $filter->campaignId);
            }

            $filter->applyCampaignAttributes($query);
        }

        return [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('campaign_status_id', $statusIds['active'] ?? 0)->count(),
            'completed' => (clone $query)->where('campaign_status_id', $statusIds['completed'] ?? 0)->count(),
            'cancelled' => (clone $query)->where('campaign_status_id', $statusIds['cancelled'] ?? 0)->count(),
            'upcoming' => (clone $query)
                ->whereIn('campaign_status_id', array_filter([
                    $statusIds['draft'] ?? null,
                    $statusIds['planned'] ?? null,
                    $statusIds['active'] ?? null,
                ]))
                ->whereDate('start_date', '>', $today)
                ->count(),
        ];
    }

    /**
     * @return Collection<int, array{
     *     campaign: Campaign,
     *     patients: int,
     *     activities: int,
     *     trips: int,
     *     attendance: int,
     * }>
     */
    public function getPerformanceComparison(int $limit = 8, ?DashboardFilter $filter = null): Collection
    {
        $query = Campaign::query()->with(['country', 'city', 'specialty', 'campaignStatus']);

        if ($filter) {
            $ids = $filter->resolvedCampaignIds();

            if ($ids !== null) {
                $query->whereIn('id', $ids);
            }

            $filter->applyCampaignAttributes($query);
        }

        return $query
            ->withCount(['patients', 'activities', 'transportationTrips', 'attendances'])
            ->orderByDesc('start_date')
            ->limit($limit)
            ->get()
            ->map(fn (Campaign $campaign): array => [
                'campaign' => $campaign,
                'patients' => $campaign->patients_count,
                'activities' => $campaign->activities_count,
                'trips' => $campaign->transportation_trips_count,
                'attendance' => $campaign->attendances_count,
            ]);
    }

    /**
     * @return array<int, array{month: string, count: int}>
     */
    public function getCampaignTrend(int $months = 6, ?DashboardFilter $filter = null): array
    {
        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $query = Campaign::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            if ($filter) {
                $ids = $filter->resolvedCampaignIds();

                if ($ids !== null) {
                    $query->whereIn('id', $ids);
                }

                $filter->applyCampaignAttributes($query);
            }

            $results[] = [
                'month' => $date->format('M'),
                'count' => $query->count(),
            ];
        }

        return $results;
    }
}
