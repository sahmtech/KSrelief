<?php

namespace App\Services\Settings;

use App\Models\City;
use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CitySettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return City::class;
    }

    public function paginate(
        ?int $countryId = null,
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return City::query()
            ->when($countryId, fn ($query) => $query->where('country_id', $countryId))
            ->search($search)
            ->status($status)
            ->with('country')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForCountry(
        Country $country,
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return $this->paginate($country->id, $search, $status, $perPage);
    }
}
