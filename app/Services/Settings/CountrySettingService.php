<?php

namespace App\Services\Settings;

use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CountrySettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return Country::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return Country::query()
            ->search($search)
            ->status($status)
            ->withCount('cities')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
