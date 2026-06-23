<?php

namespace Database\Seeders;

use App\Models\CtFindingOption;
use Illuminate\Database\Seeder;

class CtFindingOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['name' => 'Normal inner ear', 'code' => 'normal_inner_ear', 'sort_order' => 1],
            ['name' => 'IP-1', 'code' => 'ip_1', 'sort_order' => 2],
            ['name' => 'IP-2', 'code' => 'ip_2', 'sort_order' => 3],
            ['name' => 'IP-3', 'code' => 'ip_3', 'sort_order' => 4],
            ['name' => 'Mondini', 'code' => 'mondini', 'sort_order' => 5],
            ['name' => 'EVA', 'code' => 'eva', 'sort_order' => 6],
            ['name' => 'Common Cavity', 'code' => 'common_cavity', 'sort_order' => 7],
            ['name' => 'Cochlear hypoplasia', 'code' => 'cochlear_hypoplasia', 'sort_order' => 8],
        ];

        foreach ($options as $option) {
            CtFindingOption::query()->updateOrCreate(
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
