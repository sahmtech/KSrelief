<?php

namespace Database\Seeders;

use App\Models\InsertionApproach;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InsertionApproachesSeeder extends Seeder
{
    public function run(): void
    {
        $approaches = [
            ['name' => 'Round', 'code' => 'round', 'sort_order' => 1],
            ['name' => 'Window', 'code' => 'window', 'sort_order' => 2],
            ['name' => 'Cochleostomy', 'code' => 'cochleostomy', 'sort_order' => 3],
        ];

        foreach ($approaches as $approach) {
            InsertionApproach::query()->updateOrCreate(
                ['code' => $approach['code']],
                [
                    'name' => $approach['name'],
                    'sort_order' => $approach['sort_order'],
                    'status' => 'active',
                ]
            );
        }
    }
}
