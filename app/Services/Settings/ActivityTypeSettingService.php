<?php

namespace App\Services\Settings;

use App\Models\ActivityType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ActivityTypeSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return ActivityType::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return ActivityType::query()
            ->search($search)
            ->status($status)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function generateUniqueCode(string $name): string
    {
        $base = Str::upper(Str::slug(trim($name), '_'));

        if ($base === '') {
            $base = 'ACTIVITY_TYPE';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (ActivityType::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
