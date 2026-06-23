<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountriesSeeder::class,
            CitiesSeeder::class,
            SpecialtiesSeeder::class,
            MemberRolesSeeder::class,
            PatientEligibilityStatusesSeeder::class,
            PatientStagesSeeder::class,
            ActivityTypesSeeder::class,
            TransportationLocationsSeeder::class,
            AttendanceStatusesSeeder::class,
            CampaignStatusesSeeder::class,
            ImplantCompaniesSeeder::class,
            InsertionApproachesSeeder::class,
            CtFindingOptionsSeeder::class,
            MriFindingOptionsSeeder::class,
            ExpectationPostCiOptionsSeeder::class,
        ]);
    }
}
