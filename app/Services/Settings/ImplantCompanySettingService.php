<?php

namespace App\Services\Settings;

use App\Enums\SettingStatus;
use App\Models\ImplantCompany;
use App\Models\ImplantElectrodeType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ImplantCompanySettingService extends BaseSettingService
{
    protected function modelClass(): string
    {
        return ImplantCompany::class;
    }

    public function paginate(
        ?string $search = null,
        ?string $status = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        return ImplantCompany::query()
            ->withCount('electrodeTypes')
            ->search($search)
            ->status($status)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function generateUniqueCode(string $name): string
    {
        $base = Str::upper(Str::slug(trim($name), '_'));

        if ($base === '') {
            $base = 'COMPANY';
        }

        $base = Str::limit($base, 40, '');
        $code = $base;
        $suffix = 1;

        while (ImplantCompany::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 36, '').'_'.$suffix;
            $suffix++;
        }

        return $code;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?int $userId = null): Model
    {
        $electrodeTypes = $data['electrode_types'] ?? [];
        unset($data['electrode_types']);

        /** @var ImplantCompany $company */
        $company = parent::create($data, $userId);
        $this->syncElectrodeTypes($company, $electrodeTypes);

        return $company->load('electrodeTypes');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data, ?int $userId = null): Model
    {
        $electrodeTypes = $data['electrode_types'] ?? [];
        unset($data['electrode_types']);

        /** @var ImplantCompany $company */
        $company = parent::update($model, $data, $userId);
        $this->syncElectrodeTypes($company, $electrodeTypes);

        return $company->load('electrodeTypes');
    }

    /**
     * @param  list<array{id?: int|null, name?: string|null}>  $electrodeTypes
     */
    public function syncElectrodeTypes(ImplantCompany $company, array $electrodeTypes): void
    {
        $keptIds = [];
        $sortOrder = 1;

        foreach ($electrodeTypes as $row) {
            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $id = isset($row['id']) && is_numeric($row['id']) ? (int) $row['id'] : null;

            if ($id) {
                $electrode = ImplantElectrodeType::query()
                    ->where('implant_company_id', $company->id)
                    ->find($id);

                if ($electrode) {
                    $electrode->update([
                        'name' => $name,
                        'sort_order' => $sortOrder,
                        'status' => SettingStatus::Active->value,
                    ]);
                    $keptIds[] = $electrode->id;
                    $sortOrder++;

                    continue;
                }
            }

            $created = ImplantElectrodeType::query()->create([
                'implant_company_id' => $company->id,
                'name' => $name,
                'sort_order' => $sortOrder,
                'status' => SettingStatus::Active->value,
            ]);
            $keptIds[] = $created->id;
            $sortOrder++;
        }

        ImplantElectrodeType::query()
            ->where('implant_company_id', $company->id)
            ->whereNotIn('id', $keptIds)
            ->delete();
    }
}
