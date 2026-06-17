<?php

namespace Database\Seeders;

use App\Models\PatientStage;
use Illuminate\Database\Seeder;

class PatientStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Admission', 'code' => 'admission', 'color' => '#3B82F6', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'Anesthesia', 'code' => 'anesthesia', 'color' => '#8B5CF6', 'sort_order' => 2, 'is_default' => false],
            ['name' => 'Operation', 'code' => 'operation', 'color' => '#EF4444', 'sort_order' => 3, 'is_default' => false],
            ['name' => 'Post Operation', 'code' => 'post_operation', 'color' => '#F59E0B', 'sort_order' => 4, 'is_default' => false],
            ['name' => 'Activation', 'code' => 'activation', 'color' => '#14B8A6', 'sort_order' => 5, 'is_default' => false],
            ['name' => 'Rehab/Education', 'code' => 'rehab_education', 'color' => '#0F766E', 'sort_order' => 6, 'is_default' => false],
            ['name' => 'Completed', 'code' => 'completed', 'color' => '#22C55E', 'sort_order' => 7, 'is_default' => false],
        ];

        foreach ($stages as $stage) {
            PatientStage::query()->updateOrCreate(
                ['code' => $stage['code']],
                [
                    'name' => $stage['name'],
                    'color' => $stage['color'],
                    'sort_order' => $stage['sort_order'],
                    'is_default' => $stage['is_default'],
                    'status' => 'active',
                ]
            );
        }
    }
}
