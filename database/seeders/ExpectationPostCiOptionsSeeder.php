<?php

namespace Database\Seeders;

use App\Models\ExpectationPostCiOption;
use Illuminate\Database\Seeder;

class ExpectationPostCiOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['name' => 'Poor outcome', 'code' => 'poor_outcome', 'sort_order' => 1],
            ['name' => 'Good outcome', 'code' => 'good_outcome', 'sort_order' => 2],
        ];

        foreach ($options as $option) {
            ExpectationPostCiOption::query()->updateOrCreate(
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
