<?php

namespace App\Services\Settings;

use App\Models\TransportationLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransportationLocationSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return TransportationLocation::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        ?string $type = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return TransportationLocation::query()
            ->when($type, fn ($query) => $query->where('type', $type))
            ->search($search)
            ->status($status)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
