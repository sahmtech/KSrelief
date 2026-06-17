<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class WorldCountriesSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/world_countries.json');

        if (! File::exists($path)) {
            $this->command?->warn('world_countries.json missing. Run: php database/data/build_countries_json.php');

            return;
        }

        /** @var list<array{code: string, name: string}> $countries */
        $countries = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        $arabicNames = require database_path('data/countries_ar.php');

        foreach ($countries as $country) {
            Country::query()->updateOrCreate(
                ['code' => $country['code']],
                [
                    'name' => $country['name'],
                    'name_ar' => $arabicNames[$country['code']] ?? null,
                    'status' => 'active',
                ]
            );
        }
    }
}
