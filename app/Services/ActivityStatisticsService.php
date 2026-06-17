<?php

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Enums\PassengerType;
use App\Models\Activity;
use App\Models\ActivityParticipant;
use Illuminate\Support\Collection;

class ActivityStatisticsService
{
    /**
     * @return array{
     *     total: int,
     *     today: int,
     *     upcoming: int,
     *     completed: int,
     *     patients_participating: int,
     *     members_participating: int,
     *     completion_rate: int,
     * }
     */
    public function getActivityStats(?int $campaignId = null): array
    {
        $today = now()->toDateString();

        $base = Activity::query()
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId));

        $participantQuery = ActivityParticipant::query()
            ->whereHas('activity', fn ($q) => $q->when($campaignId, fn ($aq) => $aq->where('campaign_id', $campaignId)));

        $total = (clone $base)->count();
        $completed = (clone $base)->where('status', ActivityStatus::Completed->value)->count();

        return [
            'total' => $total,
            'today' => (clone $base)->whereDate('activity_date', $today)->count(),
            'upcoming' => (clone $base)
                ->whereDate('activity_date', '>=', $today)
                ->whereIn('status', [ActivityStatus::Planned->value, ActivityStatus::InProgress->value])
                ->count(),
            'completed' => $completed,
            'patients_participating' => (clone $participantQuery)
                ->where('participant_type', PassengerType::Patient->value)
                ->count(),
            'members_participating' => (clone $participantQuery)
                ->where('participant_type', PassengerType::Member->value)
                ->count(),
            'completion_rate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ];
    }

    /**
     * @return array{total: int, upcoming: int, completed: int, participants: int}
     */
    public function getCampaignActivityStats(int $campaignId): array
    {
        $stats = $this->getActivityStats($campaignId);
        $today = now()->toDateString();

        return [
            'total' => $stats['total'],
            'upcoming' => Activity::query()
                ->where('campaign_id', $campaignId)
                ->whereDate('activity_date', '>=', $today)
                ->whereIn('status', [ActivityStatus::Planned->value, ActivityStatus::InProgress->value])
                ->count(),
            'completed' => $stats['completed'],
            'participants' => $stats['patients_participating'] + $stats['members_participating'],
        ];
    }

    /**
     * @return array{total: int, upcoming: int, completed: int, attended: int}
     */
    public function getParticipantStats(int $memberId = 0, int $patientId = 0): array
    {
        $today = now()->toDateString();

        $query = ActivityParticipant::query()
            ->when($memberId, fn ($q) => $q->where('member_id', $memberId))
            ->when($patientId, fn ($q) => $q->where('patient_id', $patientId));

        $activityIds = (clone $query)->pluck('activity_id');
        $activities = Activity::query()->whereIn('id', $activityIds);

        return [
            'total' => (clone $activities)->count(),
            'upcoming' => (clone $activities)
                ->whereDate('activity_date', '>=', $today)
                ->whereIn('status', [ActivityStatus::Planned->value, ActivityStatus::InProgress->value])
                ->count(),
            'completed' => (clone $activities)->where('status', ActivityStatus::Completed->value)->count(),
            'attended' => (clone $query)->where('attendance_status', 'attended')->count(),
        ];
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getUpcomingActivities(int $limit = 5, ?int $campaignId = null): Collection
    {
        $today = now()->toDateString();

        return Activity::query()
            ->with(['campaign', 'activityType', 'creator'])
            ->withCount('participants')
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->whereDate('activity_date', '>=', $today)
            ->whereIn('status', [ActivityStatus::Planned->value, ActivityStatus::InProgress->value])
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getRecentActivities(int $limit = 5, ?int $campaignId = null): Collection
    {
        return Activity::query()
            ->with(['campaign', 'activityType', 'creator'])
            ->withCount('participants')
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getCalendarActivities(string $start, string $end, ?int $campaignId = null, ?int $activityTypeId = null): Collection
    {
        return Activity::query()
            ->with(['activityType', 'campaign'])
            ->withCount('participants')
            ->betweenDates($start, $end)
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->when($activityTypeId, fn ($q) => $q->where('activity_type_id', $activityTypeId))
            ->get();
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getMemberActivities(int $memberId, int $limit = 10): Collection
    {
        $activityIds = ActivityParticipant::query()
            ->where('member_id', $memberId)
            ->pluck('activity_id');

        return Activity::query()
            ->with(['activityType', 'campaign'])
            ->whereIn('id', $activityIds)
            ->orderByDesc('activity_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getPatientActivities(int $patientId, int $limit = 10): Collection
    {
        $activityIds = ActivityParticipant::query()
            ->where('patient_id', $patientId)
            ->pluck('activity_id');

        return Activity::query()
            ->with(['activityType', 'campaign'])
            ->whereIn('id', $activityIds)
            ->orderByDesc('activity_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{today: int, upcoming: int, completed_today: int, completion_rate: int}
     */
    public function getDashboardStats(): array
    {
        $stats = $this->getActivityStats();
        $today = now()->toDateString();

        $completedToday = Activity::query()
            ->whereDate('activity_date', $today)
            ->where('status', ActivityStatus::Completed->value)
            ->count();

        return [
            'today' => $stats['today'],
            'upcoming' => $stats['upcoming'],
            'completed_today' => $completedToday,
            'completion_rate' => $stats['completion_rate'],
            'patients_participating' => $stats['patients_participating'],
            'members_participating' => $stats['members_participating'],
        ];
    }
}
