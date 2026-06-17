<?php

namespace App\Services\Settings;

use App\Models\PatientStage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PatientStageSettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return PatientStage::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return PatientStage::query()
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
            $base = 'PATIENT_STAGE';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (PatientStage::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
