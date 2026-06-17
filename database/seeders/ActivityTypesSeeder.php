<?php

namespace Database\Seeders;

use App\Models\ActivityType;
use Illuminate\Database\Seeder;

class ActivityTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Surgery', 'code' => 'surgery', 'color' => '#EF4444'],
            ['name' => 'Activation Session', 'code' => 'activation', 'color' => '#14B8A6'],
            ['name' => 'Rehabilitation Session', 'code' => 'rehab', 'color' => '#0F766E'],
            ['name' => 'Educational Session', 'code' => 'education', 'color' => '#3B82F6'],
            ['name' => 'Team Meeting', 'code' => 'team_meeting', 'color' => '#8B5CF6'],
            ['name' => 'Medical Assessment', 'code' => 'medical_assessment', 'color' => '#F59E0B'],
            ['name' => 'Training Session', 'code' => 'training', 'color' => '#06B6D4'],
            ['name' => 'Custom Activity', 'code' => 'custom', 'color' => '#64748B'],
        ];

        foreach ($types as $type) {
            ActivityType::query()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'color' => $type['color'],
                    'status' => 'active',
                ]
            );
        }
    }
}
