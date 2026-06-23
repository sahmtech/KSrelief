<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\ImplantCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImplantCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ImplantCompany::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('implant_companies', 'code')],
            'color' => ['required', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(SettingStatus::values())],
            'electrode_types' => ['nullable', 'array'],
            'electrode_types.*.id' => ['nullable', 'integer'],
            'electrode_types.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
