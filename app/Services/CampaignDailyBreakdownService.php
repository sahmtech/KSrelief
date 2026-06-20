<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\Patient;
use App\Models\TransportationTrip;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CampaignDailyBreakdownService
{
    /**
     * @return list<array{
     *     day_number: int,
     *     date: Carbon,
     *     date_key: string,
     *     is_today: bool,
     *     assigned_members: Collection<int, CampaignMember>,
     *     attendances: Collection<int, Attendance>,
     *     patients: Collection<int, Patient>,
     *     trips: Collection<int, TransportationTrip>,
     *     activities: Collection<int, Activity>,
     *     counts: array{
     *         assigned: int,
     *         attendance: int,
     *         present: int,
     *         patients: int,
     *         trips: int,
     *         activities: int,
     *     },
     * }>
     */
    public function getDailyBreakdown(Campaign $campaign): array
    {
        if (! $campaign->start_date || ! $campaign->end_date) {
            return [];
        }

        $start = $campaign->start_date->copy()->startOfDay();
        $end = $campaign->end_date->copy()->startOfDay();
        $today = now()->startOfDay();
        $campaignId = $campaign->id;

        $assignments = CampaignMember::query()
            ->where('campaign_id', $campaignId)
            ->with(['member.memberRole', 'member.specialty'])
            ->get();

        $attendances = Attendance::query()
            ->where('campaign_id', $campaignId)
            ->whereDate('attendance_date', '>=', $start)
            ->whereDate('attendance_date', '<=', $end)
            ->with(['member.memberRole', 'attendanceStatus'])
            ->orderBy('shift_number')
            ->orderBy('check_in')
            ->get()
            ->groupBy(fn (Attendance $attendance): string => $attendance->attendance_date->format('Y-m-d'));

        $patients = Patient::query()
            ->where('campaign_id', $campaignId)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->orderBy('patient_name')
            ->get(['id', 'patient_name', 'file_number', 'created_at'])
            ->groupBy(fn (Patient $patient): string => $patient->created_at->format('Y-m-d'));

        $trips = TransportationTrip::query()
            ->where('campaign_id', $campaignId)
            ->whereDate('trip_date', '>=', $start)
            ->whereDate('trip_date', '<=', $end)
            ->with(['fromLocation', 'toLocation'])
            ->orderBy('trip_date')
            ->orderBy('departure_time')
            ->get()
            ->groupBy(fn (TransportationTrip $trip): string => $trip->trip_date->format('Y-m-d'));

        $activities = Activity::query()
            ->where('campaign_id', $campaignId)
            ->whereDate('activity_date', '>=', $start)
            ->whereDate('activity_date', '<=', $end)
            ->with('activityType')
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn (Activity $activity): string => $activity->activity_date->format('Y-m-d'));

        $presentStatusIds = AttendanceStatus::query()
            ->whereIn('code', ['present', 'late'])
            ->pluck('id');

        $days = [];
        $dayNumber = 1;
        $date = $start->copy();

        while ($date->lte($end)) {
            $dateKey = $date->format('Y-m-d');
            $dayAssignments = $assignments
                ->filter(fn (CampaignMember $assignment): bool => $assignment->isActiveOn($date))
                ->values();
            $dayAttendances = $attendances->get($dateKey, collect());
            $dayPatients = $patients->get($dateKey, collect());
            $dayTrips = $trips->get($dateKey, collect());
            $dayActivities = $activities->get($dateKey, collect());

            $days[] = [
                'day_number' => $dayNumber,
                'date' => $date->copy(),
                'date_key' => $dateKey,
                'is_today' => $date->equalTo($today),
                'assigned_members' => $dayAssignments,
                'attendances' => $dayAttendances,
                'patients' => $dayPatients,
                'trips' => $dayTrips,
                'activities' => $dayActivities,
                'counts' => [
                    'assigned' => $dayAssignments->count(),
                    'attendance' => $dayAttendances->count(),
                    'present' => $dayAttendances->whereIn('attendance_status_id', $presentStatusIds)->count(),
                    'patients' => $dayPatients->count(),
                    'trips' => $dayTrips->count(),
                    'activities' => $dayActivities->count(),
                ],
            ];

            $date->addDay();
            $dayNumber++;
        }

        return $days;
    }

    /**
     * Surgery-day schedule (matches client Day1/Day2/Day3 sheets).
     *
     * @return list<array{
     *     day_number: int,
     *     patients: Collection<int, Patient>,
     *     count: int,
     * }>
     */
    public function getSurgeryDaysSchedule(Campaign $campaign): array
    {
        $dayCount = max(1, $campaign->campaignDaysCount());
        $patients = Patient::query()
            ->where('campaign_id', $campaign->id)
            ->with(['eligibilityStatus', 'currentStage'])
            ->orderBy('rank')
            ->orderBy('patient_name')
            ->get();

        $maxAssignedDay = (int) $patients->max('surgery_day_number');
        $totalDays = max($dayCount, $maxAssignedDay);

        $schedule = [];

        for ($day = 1; $day <= $totalDays; $day++) {
            $dayPatients = $patients
                ->where('surgery_day_number', $day)
                ->values();

            $schedule[] = [
                'day_number' => $day,
                'patients' => $dayPatients,
                'count' => $dayPatients->count(),
            ];
        }

        return $schedule;
    }
}
