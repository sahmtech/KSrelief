<?php

namespace App\Services;

use App\Enums\SettingStatus;
use App\Models\ClinicalSelectOption;
use App\Models\User;
use App\Support\OperationFieldSupport;
use App\Support\PreOperationFieldSupport;
use Illuminate\Support\Str;

class ClinicalSelectOptionService
{
    /**
     * @return array<string, string>
     */
    public function optionsForCategory(string $category, ?string $configKey = null): array
    {
        $base = $configKey
            ? collect(config("patient_clinical.{$configKey}", []))
                ->mapWithKeys(fn (string $label, string $code): array => [
                    $code => str_contains($label, '.') ? __($label) : $label,
                ])
                ->all()
            : [];

        $custom = ClinicalSelectOption::query()
            ->where('category', $category)
            ->where('status', SettingStatus::Active)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'code')
            ->all();

        return $base + $custom;
    }

    public function createForCategory(string $category, string $name, User $user): ClinicalSelectOption
    {
        $code = $this->generateUniqueCode($category, $name);

        return ClinicalSelectOption::query()->create([
            'category' => $category,
            'code' => $code,
            'name' => trim($name),
            'sort_order' => (int) ClinicalSelectOption::query()->where('category', $category)->max('sort_order') + 1,
            'status' => SettingStatus::Active,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function generateUniqueCode(string $category, string $name): string
    {
        $base = Str::lower(Str::slug(trim($name), '_'));

        if ($base === '') {
            $base = 'option';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        $configKey = PreOperationFieldSupport::SELECT_CATEGORY_CONFIG[$category]
            ?? OperationFieldSupport::SELECT_CATEGORY_CONFIG[$category]
            ?? null;

        while (
            ClinicalSelectOption::query()->where('category', $category)->where('code', $code)->exists()
            || ($configKey && array_key_exists($code, config("patient_clinical.{$configKey}", [])))
        ) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }

    public function labelFor(string $category, ?string $code, ?string $configKey = null): ?string
    {
        if (! filled($code)) {
            return null;
        }

        $options = $this->optionsForCategory($category, $configKey);

        return $options[$code] ?? $code;
    }
}
