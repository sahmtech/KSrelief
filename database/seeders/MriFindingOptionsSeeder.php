<?php

namespace Database\Seeders;

use App\Models\MriFindingOption;
use Illuminate\Database\Seeder;

class MriFindingOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['name' => 'Patent cochlear', 'code' => 'patent_cochlear', 'sort_order' => 1],
            ['name' => 'Normal cochlear nerve', 'code' => 'normal_cochlear_nerve', 'sort_order' => 2],
            ['name' => 'Hypoplastic cochlear nerve', 'code' => 'hypoplastic_cochlear_nerve', 'sort_order' => 3],
            ['name' => 'Absent cochlear nerve', 'code' => 'absent_cochlear_nerve', 'sort_order' => 4],
            ['name' => 'Partial ossification', 'code' => 'partial_ossification', 'sort_order' => 5],
            ['name' => 'Complete ossification', 'code' => 'complete_ossification', 'sort_order' => 6],
        ];

        foreach ($options as $option) {
            MriFindingOption::query()->updateOrCreate(
                ['code' => $option['code']],
                [
                    'name' => $option['name'],
                    'sort_order' => $option['sort_order'],
                    'status' => 'active',
                ]
            );
        }
    }
}
