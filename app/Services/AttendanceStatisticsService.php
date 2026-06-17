<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Campaign;
use App\Models\Member;
use Illuminate\Support\Collection;

class AttendanceStatisticsService
{
    /**
     * @return array{
     *     total: int,
     *     present_today: int,
     *     late_today: int,
     *     absent_today: int,
     *     leave_today: int,
     *     attendance_rate: int,
     * }
     */
    public function getTodayStats(?int $campaignId = null): array
    {
        $today = now()->toDateString();
        $statusCodes = AttendanceStatus::query()->pluck('id', 'code');

        $query = Attendance::query()
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->whereDate('attendance_date', $today);

        $total = (clone $query)->count();
        $present = (clone $query)->where('attendance_status_id', $statusCodes['present'] ?? 0)->count();
        $late = (clone $query)->where('attendance_status_id', $statusCodes['late'] ?? 0)->count();
        $absent = (clone $query)->where('attendance_status_id', $statusCodes['absent'] ?? 0)->count();
        $leave = (clone $query)->where('attendance_status_id', $statusCodes['leave'] ?? 0)->count();

        $expected = $this->expectedMembersCount($campaignId);
        $attended = $present + $late;
        $rate = $expected > 0 ? (int) round(($attended / $expected) * 100) : ($total > 0 ? 100 : 0);

        return [
            'total' => $total,
            'present_today' => $present,
            'late_today' => $late,
            'absent_today' => $absent,
            'leave_today' => $leave,
            'attendance_rate' => min(100, $rate),
        ];
    }

    /**
     * @return array{
     *     total: int,
     *     present: int,
     *     late: int,
     *     absent: int,
     *     leave: int,
     *     attendance_rate: int,
     * }
     */
    public function getCampaignStats(int $campaignId, ?string $date = null): array
    {
        $statusCodes = AttendanceStatus::query()->pluck('id', 'code');

        $query = Attendance::query()->where('campaign_id', $campaignId);

        if ($date) {
            $query->whereDate('attendance_date', $date);
        }

        $total = (clone $query)->count();
        $present = (clone $query)->where('attendance_status_id', $statusCodes['present'] ?? 0)->count();
        $late = (clone $query)->where('attendance_status_id', $statusCodes['late'] ?? 0)->count();
        $absent = (clone $query)->where('attendance_status_id', $statusCodes['absent'] ?? 0)->count();
        $leave = (clone $query)->where('attendance_status_id', $statusCodes['leave'] ?? 0)->count();

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'leave' => $leave,
            'attendance_rate' => $this->getAttendanceRate($campaignId, $date),
        ];
    }

    /**
     * @return array{
     *     total: int,
     *     present: int,
     *     late: int,
     *     absent: int,
     *     attendance_rate: int,
     * }
     */
    public function getMemberStats(int $memberId): array
    {
        $statusCodes = AttendanceStatus::query()->pluck('id', 'code');

        $query = Attendance::query()->where('member_id', $memberId);
        $total = (clone $query)->count();
        $present = (clone $query)->where('attendance_status_id', $statusCodes['present'] ?? 0)->count();
        $late = (clone $query)->where('attendance_status_id', $statusCodes['late'] ?? 0)->count();
        $absent = (clone $query)->where('attendance_status_id', $statusCodes['absent'] ?? 0)->count();

        $attended = $present + $late;
        $rate = $total > 0 ? (int) round(($attended / $total) * 100) : 0;

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'attendance_rate' => $rate,
        ];
    }

    public function getAttendanceRate(?int $campaignId = null, ?string $date = null): int
    {
        $statusCodes = AttendanceStatus::query()->pluck('id', 'code');

        $query = Attendance::query()
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->when($date, fn ($q) => $q->whereDate('attendance_date', $date));

        $total = (clone $query)->count();

        if ($total === 0) {
            $expected = $this->expectedMembersCount($campaignId);

            return $expected > 0 ? 0 : 0;
        }

        $attended = (clone $query)->whereIn('attendance_status_id', array_filter([
            $statusCodes['present'] ?? null,
            $statusCodes['late'] ?? null,
        ]))->count();

        return (int) round(($attended / $total) * 100);
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getRecentAttendances(int $limit = 5, ?int $campaignId = null, ?int $memberId = null): Collection
    {
        return Attendance::query()
            ->with(['campaign', 'member.memberRole', 'attendanceStatus', 'recorder'])
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->when($memberId, fn ($q) => $q->where('member_id', $memberId))
            ->orderByDesc('attendance_date')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function expectedMembersCount(?int $campaignId): int
    {
        if (! $campaignId) {
            return Member::query()->where('status', 'active')->count();
        }

        return Campaign::query()
            ->find($campaignId)
            ?->campaignMemberAssignments()
            ->where(function ($query): void {
                $query->whereNull('assigned_to')
                    ->orWhereDate('assigned_to', '>=', now());
            })
            ->count() ?? 0;
    }
}
