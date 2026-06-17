<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class CampaignReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRegionalCities();
        $this->seedSpecialties();
    }

    private function seedRegionalCities(): void
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
                    ['is_active' => true]
                );
            }
        }
    }

    private function seedSpecialties(): void
    {
        $specialties = [
            ['name' => 'General Surgery', 'code' => 'general_surgery'],
            ['name' => 'Ophthalmology', 'code' => 'ophthalmology'],
            ['name' => 'Cardiology', 'code' => 'cardiology'],
            ['name' => 'Orthopedics', 'code' => 'orthopedics'],
            ['name' => 'Pediatrics', 'code' => 'pediatrics'],
            ['name' => 'Dentistry', 'code' => 'dentistry'],
        ];

        foreach ($specialties as $specialty) {
            Specialty::query()->updateOrCreate(
                ['code' => $specialty['code']],
                ['name' => $specialty['name'], 'is_active' => true]
            );
        }
    }
}
