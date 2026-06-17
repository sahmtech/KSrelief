<?php

namespace App\Services\Settings;

use App\Models\CampaignStatusRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CampaignStatusSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return CampaignStatusRecord::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return CampaignStatusRecord::query()
            ->search($search)
            ->status($status)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function generateUniqueCode(string $name): string
    {
        $base = Str::lower(Str::slug(trim($name), '_'));

        if ($base === '') {
            $base = 'campaign_status';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (CampaignStatusRecord::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
