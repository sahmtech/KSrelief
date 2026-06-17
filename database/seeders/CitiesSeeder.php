<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CitiesSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            'SAU' => ['Riyadh', 'Jeddah', 'Dammam', 'Makkah', 'Madinah'],
            'JOR' => ['Amman', 'Irbid', 'Zarqa', 'Aqaba'],
            'YEM' => ['Sanaa', 'Aden', 'Taiz', 'Hodeidah'],
            'PSE' => ['Gaza', 'Ramallah', 'Hebron', 'Nablus'],
            'EGY' => ['Cairo', 'Alexandria', 'Giza'],
            'LBN' => ['Beirut', 'Tripoli', 'Sidon'],
            'SYR' => ['Damascus', 'Aleppo', 'Homs'],
            'IRQ' => ['Baghdad', 'Basra', 'Erbil'],
            'TUR' => ['Istanbul', 'Ankara', 'Izmir'],
            'PAK' => ['Karachi', 'Lahore', 'Islamabad'],
        ];

        foreach ($regions as $countryCode => $cities) {
            $country = Country::query()->where('code', $countryCode)->first();

            if (! $country) {
                continue;
            }

            foreach ($cities as $cityName) {
                City::query()->updateOrCreate(
                    ['country_id' => $country->id, 'name' => $cityName],
                    ['status' => 'active']
                );
            }
        }
    }
}
