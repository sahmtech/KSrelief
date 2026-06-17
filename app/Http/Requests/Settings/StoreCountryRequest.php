<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Country::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:3', Rule::unique('countries', 'code')],
            'iso2' => ['nullable', 'string', 'size:2', Rule::unique('countries', 'iso2')],
            'iso3' => ['nullable', 'string', 'size:3', Rule::unique('countries', 'iso3')],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
