<?php

namespace App\Services\Settings;

use App\Models\PatientEligibilityStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PatientEligibilityStatusSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return PatientEligibilityStatus::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return PatientEligibilityStatus::query()
            ->search($search)
            ->status($status)
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function generateUniqueCode(string $name): string
    {
        $base = Str::upper(Str::slug(trim($name), '_'));

        if ($base === '') {
            $base = 'PATIENT_STATUS';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (PatientEligibilityStatus::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
