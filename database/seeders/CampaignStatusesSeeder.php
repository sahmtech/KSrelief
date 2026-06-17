<?php

namespace Database\Seeders;

use App\Models\CampaignStatusRecord;
use Illuminate\Database\Seeder;

class CampaignStatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Draft', 'code' => 'draft', 'color' => '#64748B', 'is_default' => true],
            ['name' => 'Planned', 'code' => 'planned', 'color' => '#3B82F6', 'is_default' => false],
            ['name' => 'Active', 'code' => 'active', 'color' => '#22C55E', 'is_default' => false],
            ['name' => 'Completed', 'code' => 'completed', 'color' => '#0F766E', 'is_default' => false],
            ['name' => 'Cancelled', 'code' => 'cancelled', 'color' => '#EF4444', 'is_default' => false],
        ];

        foreach ($statuses as $status) {
            CampaignStatusRecord::query()->updateOrCreate(
                ['code' => $status['code']],
                [
                    'name' => $status['name'],
                    'color' => $status['color'],
                    'is_default' => $status['is_default'],
                    'status' => 'active',
                ]
            );
        }
    }
}
