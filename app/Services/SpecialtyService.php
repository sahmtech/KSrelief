<?php

namespace App\Services;

use App\Enums\SettingStatus;
use App\Models\Specialty;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SpecialtyService
{
    /**
     * @return Collection<int, Specialty>
     */
    public function search(?string $term = null, int $limit = 100): Collection
    {
        return Specialty::query()
            ->active()
            ->search($term)
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function create(string $name): Specialty
    {
        return Specialty::query()->create([
            'name' => trim($name),
            'code' => $this->generateUniqueCode($name),
            'status' => SettingStatus::Active->value,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }

    private function generateUniqueCode(string $name): string
    {
        $base = Str::upper(Str::slug(trim($name), '_'));

        if ($base === '') {
            $base = 'SPECIALTY';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (Specialty::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }
}
