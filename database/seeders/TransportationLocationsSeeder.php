<?php

namespace Database\Seeders;

use App\Models\TransportationLocation;
use Illuminate\Database\Seeder;

class TransportationLocationsSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Campaign Hotel', 'type' => 'hotel'],
            ['name' => 'Partner Hospital', 'type' => 'hospital'],
            ['name' => 'International Airport', 'type' => 'airport'],
            ['name' => 'Other Location', 'type' => 'other'],
        ];

        foreach ($locations as $location) {
            TransportationLocation::query()->updateOrCreate(
                ['name' => $location['name'], 'type' => $location['type']],
                ['status' => 'active']
            );
        }
    }
}
