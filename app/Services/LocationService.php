<?php

namespace App\Services;

use App\Enums\SettingStatus;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

class LocationService
{
    /**
     * @return Collection<int, Country>
     */
    public function searchCountries(?string $term = null, int $limit = 100): Collection
    {
        return Country::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, City>
     */
    public function citiesForCountry(Country $country, ?string $term = null, int $limit = 100): Collection
    {
        return City::query()
            ->where('country_id', $country->id)
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function createCity(Country $country, string $name, ?string $nameAr = null): City
    {
        return City::query()->create([
            'country_id' => $country->id,
            'name' => trim($name),
            'name_ar' => filled($nameAr) ? trim($nameAr) : null,
            'status' => SettingStatus::Active->value,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }
}
