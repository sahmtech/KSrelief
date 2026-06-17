<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendancesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->first();
        $campaign = Campaign::query()->with('campaignMemberAssignments')->first();
        $statuses = AttendanceStatus::query()->pluck('id', 'code');

        if (! $admin || ! $campaign || $campaign->campaignMemberAssignments->isEmpty()) {
            return;
        }

        $presentId = $statuses['present'] ?? $statuses->first();
        $lateId = $statuses['late'] ?? $presentId;
        $absentId = $statuses['absent'] ?? $presentId;

        $members = $campaign->campaignMemberAssignments->take(4);
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $records = [
            ['date' => $today, 'shift' => 1, 'status' => $presentId, 'check_in' => '08:00', 'check_out' => '16:00'],
            ['date' => $today, 'shift' => 1, 'status' => $lateId, 'check_in' => '08:45', 'check_out' => '16:30'],
            ['date' => $today, 'shift' => 1, 'status' => $absentId, 'check_in' => null, 'check_out' => null],
            ['date' => $yesterday, 'shift' => 1, 'status' => $presentId, 'check_in' => '07:55', 'check_out' => '15:45'],
        ];

        foreach ($members->values() as $index => $assignment) {
            if (! isset($records[$index])) {
                break;
            }

            $record = $records[$index];
            $workedMinutes = null;

            if ($record['check_in'] && $record['check_out']) {
                $checkIn = strtotime($record['check_in']);
                $checkOut = strtotime($record['check_out']);
                $workedMinutes = max(0, (int) (($checkOut - $checkIn) / 60));
            }

            Attendance::query()->updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'member_id' => $assignment->member_id,
                    'attendance_date' => $record['date'],
                    'shift_number' => $record['shift'],
                ],
                [
                    'check_in' => $record['check_in'],
                    'check_out' => $record['check_out'],
                    'attendance_status_id' => $record['status'],
                    'worked_minutes' => $workedMinutes,
                    'notes' => __('attendance.messages.created'),
                    'recorded_by' => $admin->id,
                ]
            );
        }
    }
}
