<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\ImplantCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateImplantCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ImplantCompany $company */
        $company = $this->route('implant_company');

        return $this->user()?->can('update', $company) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ImplantCompany $company */
        $company = $this->route('implant_company');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('implant_companies', 'code')->ignore($company->id)],
            'color' => ['required', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(SettingStatus::values())],
            'electrode_types' => ['nullable', 'array'],
            'electrode_types.*.id' => ['nullable', 'integer'],
            'electrode_types.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
