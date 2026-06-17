<?php

namespace App\Services;

use App\Enums\PassengerType;
use App\Enums\TripStatus;
use App\Models\TransportationTrip;
use App\Models\TransportationTripPassenger;
use Illuminate\Support\Collection;

class TransportationStatisticsService
{
    /**
     * @return array{
     *     total: int,
     *     today: int,
     *     upcoming: int,
     *     completed: int,
     *     patients_transported: int,
     *     members_transported: int,
     * }
     */
    public function getTripStats(?int $campaignId = null): array
    {
        $today = now()->toDateString();

        $base = TransportationTrip::query()
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId));

        $passengerQuery = TransportationTripPassenger::query()
            ->whereHas('trip', fn ($q) => $q->when($campaignId, fn ($tq) => $tq->where('campaign_id', $campaignId)));

        return [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('trip_date', $today)->count(),
            'upcoming' => (clone $base)
                ->whereDate('trip_date', '>=', $today)
                ->whereIn('status', [TripStatus::Planned->value, TripStatus::InProgress->value])
                ->count(),
            'completed' => (clone $base)->where('status', TripStatus::Completed->value)->count(),
            'patients_transported' => (clone $passengerQuery)
                ->where('passenger_type', PassengerType::Patient->value)
                ->count(),
            'members_transported' => (clone $passengerQuery)
                ->where('passenger_type', PassengerType::Member->value)
                ->count(),
        ];
    }

    /**
     * @return array{
     *     total: int,
     *     upcoming: int,
     *     completed: int,
     *     passengers_transported: int,
     * }
     */
    public function getCampaignTripStats(int $campaignId): array
    {
        $stats = $this->getTripStats($campaignId);
        $today = now()->toDateString();

        return [
            'total' => $stats['total'],
            'upcoming' => TransportationTrip::query()
                ->where('campaign_id', $campaignId)
                ->whereDate('trip_date', '>=', $today)
                ->whereIn('status', [TripStatus::Planned->value, TripStatus::InProgress->value])
                ->count(),
            'completed' => $stats['completed'],
            'passengers_transported' => $stats['patients_transported'] + $stats['members_transported'],
        ];
    }

    /**
     * @return array{total: int, upcoming: int, completed: int}
     */
    public function getPatientTransportStats(int $patientId): array
    {
        $today = now()->toDateString();

        $tripIds = TransportationTripPassenger::query()
            ->where('patient_id', $patientId)
            ->pluck('trip_id');

        $trips = TransportationTrip::query()->whereIn('id', $tripIds);

        return [
            'total' => (clone $trips)->count(),
            'upcoming' => (clone $trips)
                ->whereDate('trip_date', '>=', $today)
                ->whereIn('status', [TripStatus::Planned->value, TripStatus::InProgress->value])
                ->count(),
            'completed' => (clone $trips)->where('status', TripStatus::Completed->value)->count(),
        ];
    }

    /**
     * @return array{total: int, upcoming: int, completed: int}
     */
    public function getMemberTransportStats(int $memberId): array
    {
        $today = now()->toDateString();

        $tripIds = TransportationTripPassenger::query()
            ->where('member_id', $memberId)
            ->pluck('trip_id');

        $trips = TransportationTrip::query()->whereIn('id', $tripIds);

        return [
            'total' => (clone $trips)->count(),
            'upcoming' => (clone $trips)
                ->whereDate('trip_date', '>=', $today)
                ->whereIn('status', [TripStatus::Planned->value, TripStatus::InProgress->value])
                ->count(),
            'completed' => (clone $trips)->where('status', TripStatus::Completed->value)->count(),
        ];
    }

    /**
     * @return Collection<int, TransportationTrip>
     */
    public function getRecentTrips(int $limit = 5, ?int $campaignId = null): Collection
    {
        return TransportationTrip::query()
            ->with(['campaign', 'fromLocation', 'toLocation', 'creator'])
            ->withCount('passengers')
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->orderByDesc('trip_date')
            ->orderByDesc('departure_time')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, TransportationTrip>
     */
    public function getPatientTrips(int $patientId, int $limit = 10): Collection
    {
        $tripIds = TransportationTripPassenger::query()
            ->where('patient_id', $patientId)
            ->pluck('trip_id');

        return TransportationTrip::query()
            ->with(['fromLocation', 'toLocation', 'campaign'])
            ->whereIn('id', $tripIds)
            ->orderByDesc('trip_date')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, TransportationTrip>
     */
    public function getMemberTrips(int $memberId, int $limit = 10): Collection
    {
        $tripIds = TransportationTripPassenger::query()
            ->where('member_id', $memberId)
            ->pluck('trip_id');

        return TransportationTrip::query()
            ->with(['fromLocation', 'toLocation', 'campaign'])
            ->whereIn('id', $tripIds)
            ->orderByDesc('trip_date')
            ->limit($limit)
            ->get();
    }

  /**
     * @return array{today_trips: int, upcoming: int, passengers_today: int, completed_today: int}
     */
    public function getDashboardStats(): array
    {
        $today = now()->toDateString();

        $todayTrips = TransportationTrip::query()->whereDate('trip_date', $today);

        $todayTripIds = (clone $todayTrips)->pluck('id');

        return [
            'today_trips' => (clone $todayTrips)->count(),
            'upcoming' => TransportationTrip::query()
                ->whereDate('trip_date', '>', $today)
                ->whereIn('status', [TripStatus::Planned->value, TripStatus::InProgress->value])
                ->count(),
            'passengers_today' => TransportationTripPassenger::query()
                ->whereIn('trip_id', $todayTripIds)
                ->count(),
            'completed_today' => (clone $todayTrips)
                ->where('status', TripStatus::Completed->value)
                ->count(),
        ];
    }
}
