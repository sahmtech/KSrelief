<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Present', 'code' => 'present', 'color' => '#22C55E'],
            ['name' => 'Late', 'code' => 'late', 'color' => '#F59E0B'],
            ['name' => 'Absent', 'code' => 'absent', 'color' => '#EF4444'],
            ['name' => 'Leave', 'code' => 'leave', 'color' => '#64748B'],
            ['name' => 'Replacement', 'code' => 'replacement', 'color' => '#3B82F6'],
            ['name' => 'External Mission', 'code' => 'external_mission', 'color' => '#8B5CF6'],
        ];

        foreach ($statuses as $status) {
            AttendanceStatus::query()->updateOrCreate(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'color' => $status['color'],
                    'status' => 'active',
                ]
            );
        }
    }
}
