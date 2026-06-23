<?php

namespace App\Services\Settings;

use App\Models\MriFindingOption;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class MriFindingOptionSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return MriFindingOption::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return MriFindingOption::query()
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
            $base = 'mri_finding';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (MriFindingOption::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
