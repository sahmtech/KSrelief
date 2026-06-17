<?php

namespace App\Services\Settings;

use App\Models\AttendanceStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class AttendanceStatusSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return AttendanceStatus::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return AttendanceStatus::query()
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
            $base = 'ATTENDANCE_STATUS';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (AttendanceStatus::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
