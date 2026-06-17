<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtiesSeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            ['name' => 'ENT/Otology', 'code' => 'ent_otology'],
            ['name' => 'Anesthesiology', 'code' => 'anesthesiology'],
            ['name' => 'Audiology', 'code' => 'audiology'],
            ['name' => 'Clinical Engineering', 'code' => 'clinical_engineering'],
            ['name' => 'Speech Therapy', 'code' => 'speech_therapy'],
            ['name' => 'Rehabilitation', 'code' => 'rehabilitation'],
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
                [
                    'name' => $specialty['name'],
                    'status' => 'active',
                ]
            );
        }
    }
}
