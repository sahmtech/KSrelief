<?php

namespace App\Services\Settings;

use App\Models\InsertionApproach;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class InsertionApproachSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return InsertionApproach::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return InsertionApproach::query()
            ->search($search)
            ->status($status)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function generateUniqueCode(string $name): string
    {
        $base = Str::lower(Str::slug(trim($name), '_'));

        if ($base === '') {
            $base = 'approach';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (InsertionApproach::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
