<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CampaignMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttendanceService
{
    public const AUDIT_CREATED = 'attendance.created';

    public const AUDIT_UPDATED = 'attendance.updated';

    public const AUDIT_DELETED = 'attendance.deleted';

    public const AUDIT_STATUS_CHANGED = 'attendance.status.changed';

    public const AUDIT_CHECK_IN = 'attendance.check_in';

    public const AUDIT_CHECK_OUT = 'attendance.check_out';

    public function createAttendance(array $data, User $user): Attendance
    {
        $this->assertMemberAssignedToCampaign((int) $data['campaign_id'], (int) $data['member_id']);

        return DB::transaction(function () use ($data, $user): Attendance {
            $workedMinutes = $this->calculateWorkedMinutes(
                $data['attendance_date'],
                $data['check_in'] ?? null,
                $data['check_out'] ?? null
            );

            $attendance = Attendance::create([
                'campaign_id' => $data['campaign_id'],
                'member_id' => $data['member_id'],
                'attendance_date' => $data['attendance_date'],
                'shift_number' => $data['shift_number'] ?? 1,
                'check_in' => $data['check_in'] ?? null,
                'check_out' => $data['check_out'] ?? null,
                'attendance_status_id' => $data['attendance_status_id'],
                'worked_minutes' => $workedMinutes,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $user->id,
            ]);

            // Future: dispatch audit event self::AUDIT_CREATED

            return $attendance->load([
                'campaign',
                'member.memberRole',
                'member.specialty',
                'attendanceStatus',
                'recorder',
            ]);
        });
    }

    public function updateAttendance(Attendance $attendance, array $data, User $user): Attendance
    {
        if (isset($data['member_id'], $data['campaign_id'])) {
            $this->assertMemberAssignedToCampaign(
                (int) $data['campaign_id'],
                (int) $data['member_id']
            );
        }

        return DB::transaction(function () use ($attendance, $data, $user): Attendance {
            $attendanceDate = $data['attendance_date'] ?? $attendance->attendance_date->format('Y-m-d');
            $checkIn = array_key_exists('check_in', $data) ? $data['check_in'] : $attendance->check_in;
            $checkOut = array_key_exists('check_out', $data) ? $data['check_out'] : $attendance->check_out;

            $attendance->update([
                'campaign_id' => $data['campaign_id'] ?? $attendance->campaign_id,
                'member_id' => $data['member_id'] ?? $attendance->member_id,
                'attendance_date' => $attendanceDate,
                'shift_number' => $data['shift_number'] ?? $attendance->shift_number,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'attendance_status_id' => $data['attendance_status_id'] ?? $attendance->attendance_status_id,
                'worked_minutes' => $this->calculateWorkedMinutes($attendanceDate, $checkIn, $checkOut),
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $attendance->notes,
            ]);

            // Future: dispatch audit events for status/check changes

            return $attendance->fresh([
                'campaign',
                'member.memberRole',
                'member.specialty',
                'attendanceStatus',
                'recorder',
            ]);
        });
    }

    public function checkIn(Attendance $attendance, string $checkIn, User $user, ?int $statusId = null): Attendance
    {
        return $this->updateAttendance($attendance, [
            'check_in' => $checkIn,
            'attendance_status_id' => $statusId ?? $attendance->attendance_status_id,
        ], $user);
    }

    public function checkOut(Attendance $attendance, string $checkOut, User $user, ?string $notes = null): Attendance
    {
        $payload = ['check_out' => $checkOut];

        if ($notes !== null) {
            $payload['notes'] = $notes;
        }

        return $this->updateAttendance($attendance, $payload, $user);
    }

    public function deleteAttendance(Attendance $attendance): void
    {
        DB::transaction(function () use ($attendance): void {
            // Future: dispatch audit event self::AUDIT_DELETED
            $attendance->delete();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{created: int, updated: int}
     */
    public function bulkStore(array $rows, User $user): array
    {
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($rows, $user, &$created, &$updated): void {
            foreach ($rows as $row) {
                if (empty($row['member_id'])) {
                    continue;
                }

                $this->assertMemberAssignedToCampaign(
                    (int) $row['campaign_id'],
                    (int) $row['member_id']
                );

                $existing = Attendance::query()
                    ->where('campaign_id', $row['campaign_id'])
                    ->where('member_id', $row['member_id'])
                    ->whereDate('attendance_date', $row['attendance_date'])
                    ->where('shift_number', $row['shift_number'] ?? 1)
                    ->first();

                if ($existing) {
                    $this->updateAttendance($existing, $row, $user);
                    $updated++;
                } else {
                    $this->createAttendance($row, $user);
                    $created++;
                }
            }
        });

        return ['created' => $created, 'updated' => $updated];
    }

    public function calculateWorkedMinutes(
        string $attendanceDate,
        ?string $checkIn,
        ?string $checkOut
    ): ?int {
        if (! filled($checkIn) || ! filled($checkOut)) {
            return null;
        }

        $date = Carbon::parse($attendanceDate)->format('Y-m-d');
        $in = Carbon::parse("{$date} {$checkIn}");
        $out = Carbon::parse("{$date} {$checkOut}");

        if ($out->lessThanOrEqualTo($in)) {
            $out->addDay();
        }

        return (int) $in->diffInMinutes($out);
    }

    public function assertMemberAssignedToCampaign(int $campaignId, int $memberId): void
    {
        $assigned = CampaignMember::query()
            ->where('campaign_id', $campaignId)
            ->where('member_id', $memberId)
            ->where(function ($query): void {
                $query->whereNull('assigned_to')
                    ->orWhereDate('assigned_to', '>=', now());
            })
            ->exists();

        if (! $assigned) {
            throw new InvalidArgumentException(__('attendance.errors.member_not_assigned'));
        }
    }
}
