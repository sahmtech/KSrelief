<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Enums\PatientImportBatchStatus;
use App\Enums\TripStatus;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\Member;
use App\Models\Patient;
use App\Models\PatientImportBatch;
use App\Models\PatientStage;
use App\Models\PatientStageHistory;
use App\Models\TransportationTrip;
use App\Models\User;
use App\Support\DashboardFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardStatisticsService
{
    public function __construct(
        private readonly CampaignStatisticsService $campaignStatisticsService,
        private readonly PatientStatisticsService $patientStatisticsService,
        private readonly AttendanceStatisticsService $attendanceStatisticsService,
        private readonly TransportationStatisticsService $transportationStatisticsService,
        private readonly ActivityStatisticsService $activityStatisticsService,
        private readonly MemberService $memberService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getExecutiveData(User $user, DashboardFilter $filter): array
    {
        $cacheKey = sprintf('dashboard.executive.%s.%s', $user->id, $filter->cacheKey());

        return Cache::remember($cacheKey, now()->addSeconds(60), fn (): array => [
            'overview' => $this->getOverviewKPIs($filter),
            'campaigns' => $this->getCampaignKPIs($filter),
            'patients' => $this->getPatientKPIs($filter),
            'workflow' => $this->getWorkflowKPIs($filter),
            'members' => $this->getMemberKPIs($filter),
            'attendance' => $this->getAttendanceKPIs($filter),
            'transportation' => $this->getTransportationKPIs($filter),
            'activities' => $this->getActivityKPIs($filter),
            'imports' => $this->getImportKPIs($filter),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCampaignDashboardData(Campaign $campaign): array
    {
        $filter = (new DashboardFilter(campaignId: $campaign->id))->forCampaign($campaign->id);
        $cacheKey = 'dashboard.campaign.'.$campaign->id;

        return Cache::remember($cacheKey, now()->addSeconds(60), fn (): array => [
            'campaign' => $campaign->load(['country', 'city', 'specialty', 'campaignStatus']),
            'overview' => $this->getOverviewKPIs($filter),
            'patients' => $this->getPatientKPIs($filter),
            'workflow' => $this->getWorkflowKPIs($filter),
            'members' => $this->getMemberKPIs($filter),
            'attendance' => $this->getAttendanceKPIs($filter),
            'transportation' => $this->getTransportationKPIs($filter),
            'activities' => $this->getActivityKPIs($filter),
            'imports' => $this->getImportKPIs($filter),
        ]);
    }

    /** @return array<string, int|float> */
    public function getOverviewKPIs(DashboardFilter $filter): array
    {
        $campaigns = $this->campaignStatisticsService->getStats($filter);
        $patients = $this->getPatientKPIs($filter);

        return [
            'total_campaigns' => $campaigns['total'],
            'active_campaigns' => $campaigns['active'],
            'completed_campaigns' => $campaigns['completed'],
            'cancelled_campaigns' => $campaigns['cancelled'],
            'total_patients' => $patients['total'],
            'workflow_completion_rate' => $this->getWorkflowKPIs($filter)['completion_rate'],
        ];
    }

    /** @return array<string, int> */
    public function getCampaignKPIs(DashboardFilter $filter): array
    {
        return $this->campaignStatisticsService->getStats($filter);
    }

    /** @return array<string, int> */
    public function getPatientKPIs(DashboardFilter $filter): array
    {
        $campaignId = $this->singleCampaignId($filter);

        return $this->patientStatisticsService->getPatientCounts($campaignId);
    }

    /** @return array<string, mixed> */
    public function getWorkflowKPIs(DashboardFilter $filter): array
    {
        $campaignId = $this->singleCampaignId($filter);
        $counts = $this->patientStatisticsService->getPatientCounts($campaignId);
        $stageStats = $this->patientStatisticsService->getStageStats($campaignId);
        $stageCounts = $stageStats->keyBy(fn (array $row) => $row['stage']->code);

        $waitingAdmission = $this->countPatientsInStageCodes($filter, ['admission']);
        $waitingOperation = $this->countPatientsInStageCodes($filter, ['anesthesia', 'operation']);
        $waitingActivation = $this->countPatientsInStageCodes($filter, ['activation']);
        $inRehab = $this->countPatientsInStageCodes($filter, ['rehab_education', 'rehab', 'education']);

        $total = $counts['total'];
        $completed = $counts['completed'];

        return [
            'patients_by_stage' => $stageStats,
            'waiting_admission' => $waitingAdmission,
            'waiting_operation' => $waitingOperation,
            'waiting_activation' => $waitingActivation,
            'in_rehabilitation' => $inRehab,
            'completed_cases' => $completed,
            'patients_waiting' => max(0, $total - $completed),
            'total_patients' => $total,
            'workflow_progress' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'completion_rate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'stage_counts' => $stageCounts,
        ];
    }

    /** @return array<string, int> */
    public function getMemberKPIs(DashboardFilter $filter): array
    {
        $stats = $this->memberService->getDashboardStats();
        $campaignId = $this->singleCampaignId($filter);

        if ($campaignId) {
            $assigned = CampaignMember::query()
                ->where('campaign_id', $campaignId)
                ->where(function ($query): void {
                    $query->whereNull('assigned_to')
                        ->orWhereDate('assigned_to', '>=', now());
                })
                ->distinct('member_id')
                ->count('member_id');

            return [
                'total' => $assigned,
                'active' => $assigned,
                'doctors' => Member::query()
                    ->whereIn('id', CampaignMember::query()->where('campaign_id', $campaignId)->pluck('member_id'))
                    ->whereHas('memberRole', fn ($q) => $q->where('code', 'doctor'))
                    ->count(),
                'specialists' => Member::query()
                    ->whereIn('id', CampaignMember::query()->where('campaign_id', $campaignId)->pluck('member_id'))
                    ->whereHas('memberRole', fn ($q) => $q->where('code', 'specialist'))
                    ->count(),
                'coordinators' => Member::query()
                    ->whereIn('id', CampaignMember::query()->where('campaign_id', $campaignId)->pluck('member_id'))
                    ->whereHas('memberRole', fn ($q) => $q->where('code', 'coordinator'))
                    ->count(),
                'assigned' => $assigned,
                'available' => max(0, $stats['active'] - $stats['assigned_to_campaigns']),
            ];
        }

        return [
            'total' => $stats['total'],
            'active' => $stats['active'],
            'doctors' => $stats['doctors'],
            'specialists' => $stats['specialists'],
            'coordinators' => $stats['coordinators'],
            'assigned' => $stats['assigned_to_campaigns'],
            'available' => max(0, $stats['active'] - $stats['assigned_to_campaigns']),
        ];
    }

    /** @return array<string, int|float> */
    public function getAttendanceKPIs(DashboardFilter $filter): array
    {
        $campaignId = $this->singleCampaignId($filter);
        $today = $this->attendanceStatisticsService->getTodayStats($campaignId);

        return [
            ...$today,
            'monthly_attendance_rate' => $this->monthlyAttendanceRate($filter),
        ];
    }

    /** @return array<string, int> */
    public function getTransportationKPIs(DashboardFilter $filter): array
    {
        $campaignId = $this->singleCampaignId($filter);
        $stats = $this->transportationStatisticsService->getTripStats($campaignId);
        $today = now()->toDateString();

        $base = TransportationTrip::query()
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->when(! $campaignId && $filter->resolvedCampaignIds(), fn ($q) => $q->whereIn('campaign_id', $filter->resolvedCampaignIds()));

        return [
            'today_trips' => (clone $base)->whereDate('trip_date', $today)->count(),
            'upcoming_trips' => $stats['upcoming'],
            'completed_trips' => $stats['completed'],
            'cancelled_trips' => (clone $base)->where('status', TripStatus::Cancelled->value)->count(),
            'patients_transported' => $stats['patients_transported'],
            'members_transported' => $stats['members_transported'],
            'total' => $stats['total'],
        ];
    }

    /** @return array<string, int|float> */
    public function getActivityKPIs(DashboardFilter $filter): array
    {
        $campaignId = $this->singleCampaignId($filter);
        $stats = $this->activityStatisticsService->getActivityStats($campaignId);
        $today = now()->toDateString();

        $base = Activity::query()
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->when(! $campaignId && $filter->resolvedCampaignIds(), fn ($q) => $q->whereIn('campaign_id', $filter->resolvedCampaignIds()));

        $completedToday = (clone $base)
            ->whereDate('activity_date', $today)
            ->where('status', ActivityStatus::Completed->value)
            ->count();

        return [
            'today' => $stats['today'],
            'upcoming' => $stats['upcoming'],
            'completed' => $stats['completed'],
            'completed_today' => $completedToday,
            'cancelled' => (clone $base)->where('status', ActivityStatus::Cancelled->value)->count(),
            'completion_rate' => $stats['completion_rate'],
            'patients_participating' => $stats['patients_participating'],
            'members_participating' => $stats['members_participating'],
        ];
    }

    /** @return array<string, int> */
    public function getImportKPIs(DashboardFilter $filter): array
    {
        $query = PatientImportBatch::query();
        $this->applyCampaignConstraint($query, $filter, 'campaign_id');

        return [
            'total' => (clone $query)->count(),
            'pending_review' => (clone $query)->where('status', PatientImportBatchStatus::Review)->count(),
            'completed' => (clone $query)->where('status', PatientImportBatchStatus::Completed)->count(),
            'failed' => (clone $query)->where('status', PatientImportBatchStatus::Failed)->count(),
            'patients_imported' => (clone $query)
                ->where('status', PatientImportBatchStatus::Completed)
                ->sum('imported_count'),
        ];
    }

    /**
     * @return array<int, array{month: string, count: int}>
     */
    public function getPatientRegistrationTrend(DashboardFilter $filter, int $months = 6): array
    {
        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $query = Patient::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            $this->applyCampaignConstraint($query, $filter, 'campaign_id');

            $results[] = [
                'month' => $date->format('M'),
                'count' => $query->count(),
            ];
        }

        return $results;
    }

    /**
     * @return array<int, array{month: string, rate: int}>
     */
    public function getAttendanceTrend(DashboardFilter $filter, int $months = 6): array
    {
        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $query = Attendance::query()
                ->whereYear('attendance_date', $date->year)
                ->whereMonth('attendance_date', $date->month);

            $this->applyCampaignConstraint($query, $filter, 'campaign_id');

            $total = (clone $query)->count();
            $rate = $total > 0
                ? (int) round(((clone $query)->whereHas('attendanceStatus', fn ($q) => $q->whereIn('code', ['present', 'late']))->count() / $total) * 100)
                : 0;

            $results[] = ['month' => $date->format('M'), 'rate' => $rate];
        }

        return $results;
    }

    /**
     * @return array<int, array{month: string, count: int}>
     */
    public function getActivityTrend(DashboardFilter $filter, int $months = 6): array
    {
        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $query = Activity::query()
                ->whereYear('activity_date', $date->year)
                ->whereMonth('activity_date', $date->month);

            $this->applyCampaignConstraint($query, $filter, 'campaign_id');

            $results[] = ['month' => $date->format('M'), 'count' => $query->count()];
        }

        return $results;
    }

    /**
     * @return array<int, array{month: string, count: int}>
     */
    public function getTransportationTrend(DashboardFilter $filter, int $months = 6): array
    {
        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $query = TransportationTrip::query()
                ->whereYear('trip_date', $date->year)
                ->whereMonth('trip_date', $date->month);

            $this->applyCampaignConstraint($query, $filter, 'campaign_id');

            $results[] = ['month' => $date->format('M'), 'count' => $query->count()];
        }

        return $results;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getRecentFeed(DashboardFilter $filter, User $user, int $limit = 10): Collection
    {
        $campaignId = $this->singleCampaignId($filter);
        $items = collect();

        if ($user->can('patient.view')) {
            Patient::query()
                ->with(['campaign', 'currentStage'])
                ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
                ->when(! $campaignId && $filter->resolvedCampaignIds(), fn ($q) => $q->whereIn('campaign_id', $filter->resolvedCampaignIds()))
                ->latest()
                ->limit(5)
                ->get()
                ->each(fn (Patient $patient) => $items->push([
                    'type' => 'patient_registered',
                    'title' => $patient->patient_name,
                    'meta' => $patient->campaign?->name,
                    'at' => $patient->created_at,
                    'icon' => 'ti-user-heart',
                    'url' => route('patients.show', $patient),
                ]));
        }

        if ($user->can('stage.view')) {
            PatientStageHistory::query()
                ->with(['patient.campaign', 'toStage', 'changedBy'])
                ->when($campaignId, fn ($q) => $q->whereHas('patient', fn ($pq) => $pq->where('campaign_id', $campaignId)))
                ->latest('changed_at')
                ->limit(5)
                ->get()
                ->each(fn (PatientStageHistory $history) => $items->push([
                    'type' => 'stage_change',
                    'title' => $history->patient?->patient_name,
                    'meta' => $history->toStage?->name,
                    'at' => $history->changed_at,
                    'icon' => 'ti-route',
                    'url' => route('patients.show', $history->patient_id),
                ]));
        }

        if ($user->can('attendance.view')) {
            $this->attendanceStatisticsService->getRecentAttendances(5, $campaignId)
                ->each(fn ($attendance) => $items->push([
                    'type' => 'attendance',
                    'title' => $attendance->member?->full_name,
                    'meta' => $attendance->attendanceStatus?->name,
                    'at' => $attendance->created_at,
                    'icon' => 'ti-clipboard-check',
                    'url' => route('operations.attendance.index'),
                ]));
        }

        if ($user->can('transportation.view')) {
            $this->transportationStatisticsService->getRecentTrips(5, $campaignId)
                ->each(fn ($trip) => $items->push([
                    'type' => 'transportation',
                    'title' => $trip->trip_code,
                    'meta' => $trip->campaign?->name,
                    'at' => $trip->created_at,
                    'icon' => 'ti-bus',
                    'url' => route('operations.transportation.show', $trip),
                ]));
        }

        if ($user->can('activity.view')) {
            $this->activityStatisticsService->getRecentActivities(5, $campaignId)
                ->each(fn ($activity) => $items->push([
                    'type' => 'activity',
                    'title' => $activity->title,
                    'meta' => $activity->activityType?->name,
                    'at' => $activity->created_at,
                    'icon' => 'ti-activity',
                    'url' => route('operations.activities.show', $activity),
                ]));
        }

        return $items->sortByDesc('at')->take($limit)->values();
    }

    /**
     * @return array<string, Collection>
     */
    public function getUpcomingEvents(DashboardFilter $filter, User $user): array
    {
        $campaignId = $this->singleCampaignId($filter);
        $today = now()->toDateString();

        $campaigns = collect();
        if ($user->can('campaign.view')) {
            $campaignQuery = Campaign::query()
                ->whereDate('start_date', '>=', $today)
                ->orderBy('start_date')
                ->limit(5);

            if ($campaignId) {
                $campaignQuery->where('id', $campaignId);
            } elseif ($ids = $filter->resolvedCampaignIds()) {
                $campaignQuery->whereIn('id', $ids);
            }

            $campaigns = $campaignQuery->get();
        }

        return [
            'activities' => $user->can('activity.view')
                ? $this->activityStatisticsService->getUpcomingActivities(5, $campaignId)
                : collect(),
            'trips' => $user->can('transportation.view')
                ? $this->transportationStatisticsService->getRecentTrips(5, $campaignId)
                    ->filter(fn ($trip) => $trip->trip_date?->toDateString() >= $today)
                    ->values()
                : collect(),
            'campaigns' => $campaigns,
        ];
    }

    /** @return array<string, mixed> */
    public function getFilterOptions(DashboardFilter $filter): array
    {
        $campaignQuery = Campaign::query()->orderBy('name');

        if ($ids = $filter->scopedCampaignIds) {
            $campaignQuery->whereIn('id', $ids);
        }

        return [
            'campaigns' => $campaignQuery->get(['id', 'name']),
            'countries' => \App\Models\Country::query()->orderBy('name')->get(['id', 'name', 'name_ar']),
            'cities' => \App\Models\City::query()->when($filter->countryId, fn ($q) => $q->where('country_id', $filter->countryId))->orderBy('name')->get(['id', 'name', 'name_ar', 'country_id']),
            'specialties' => \App\Models\Specialty::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function singleCampaignId(DashboardFilter $filter): ?int
    {
        return $filter->campaignId;
    }

    /** @param  list<string>  $codes */
    private function countPatientsInStageCodes(DashboardFilter $filter, array $codes): int
    {
        $stageIds = PatientStage::query()->whereIn('code', $codes)->pluck('id');

        if ($stageIds->isEmpty()) {
            return 0;
        }

        $query = Patient::query()->whereIn('current_stage_id', $stageIds);
        $this->applyCampaignConstraint($query, $filter, 'campaign_id');

        return $query->count();
    }

    private function monthlyAttendanceRate(DashboardFilter $filter): int
    {
        $start = now()->startOfMonth()->toDateString();
        $query = Attendance::query()->whereDate('attendance_date', '>=', $start);
        $this->applyCampaignConstraint($query, $filter, 'campaign_id');

        $total = (clone $query)->count();

        if ($total === 0) {
            return 0;
        }

        $attended = (clone $query)->whereHas('attendanceStatus', fn ($q) => $q->whereIn('code', ['present', 'late']))->count();

        return (int) round(($attended / $total) * 100);
    }

    private function applyCampaignConstraint(Builder $query, DashboardFilter $filter, string $column): void
    {
        if ($filter->campaignId) {
            $query->where($column, $filter->campaignId);

            return;
        }

        if ($ids = $filter->resolvedCampaignIds()) {
            $query->whereIn($column, $ids);
        }
    }
}
