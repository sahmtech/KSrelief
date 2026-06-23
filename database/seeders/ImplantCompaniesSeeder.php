<?php

namespace Database\Seeders;

use App\Models\ImplantCompany;
use App\Models\ImplantElectrodeType;
use Illuminate\Database\Seeder;

class ImplantCompaniesSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Medel',
                'code' => 'medel',
                'color' => '#DC2626',
                'sort_order' => 1,
                'electrodes' => [
                    'Flex 28',
                    'Flex 26',
                    'Form 24',
                    'Form 19',
                    'Compressed',
                ],
            ],
            [
                'name' => 'Cochlear',
                'code' => 'cochlear',
                'color' => '#EAB308',
                'sort_order' => 2,
                'electrodes' => [
                    'CI522 Slim Straight',
                    'CI532 Slim Modular',
                    'CI512 Contour Advance',
                ],
            ],
        ];

        foreach ($companies as $index => $companyData) {
            $electrodes = $companyData['electrodes'];
            unset($companyData['electrodes']);

            $company = ImplantCompany::query()->updateOrCreate(
                ['code' => $companyData['code']],
                [
                    ...$companyData,
                    'status' => 'active',
                ]
            );

            foreach ($electrodes as $sortOrder => $name) {
                ImplantElectrodeType::query()->updateOrCreate(
                    [
                        'implant_company_id' => $company->id,
                        'name' => $name,
                    ],
                    [
                        'sort_order' => $sortOrder + 1,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
