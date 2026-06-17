<?php

namespace Database\Seeders;

use App\Models\PatientEligibilityStatus;
use Illuminate\Database\Seeder;

class PatientEligibilityStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Accepted', 'code' => 'accepted', 'color' => '#22C55E', 'sort_order' => 1],
            ['name' => 'Rejected', 'code' => 'rejected', 'color' => '#EF4444', 'sort_order' => 2],
            ['name' => 'Postponed', 'code' => 'postponed', 'color' => '#F59E0B', 'sort_order' => 3],
            ['name' => 'Cancelled', 'code' => 'cancelled', 'color' => '#64748B', 'sort_order' => 4],
        ];

        foreach ($statuses as $status) {
            PatientEligibilityStatus::query()->updateOrCreate(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'status' => 'active',
                ]
            );
        }
    }
}
