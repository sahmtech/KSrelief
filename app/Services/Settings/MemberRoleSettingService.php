<?php

namespace App\Services\Settings;

use App\Models\MemberRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class MemberRoleSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return MemberRole::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return MemberRole::query()
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
            $base = 'MEMBER_ROLE';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (MemberRole::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
