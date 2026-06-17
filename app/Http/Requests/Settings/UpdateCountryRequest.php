<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Country $country */
        $country = $this->route('country');

        return $this->user()?->can('update', $country) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Country $country */
        $country = $this->route('country');

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:3', Rule::unique('countries', 'code')->ignore($country->id)],
            'iso2' => ['nullable', 'string', 'size:2', Rule::unique('countries', 'iso2')->ignore($country->id)],
            'iso3' => ['nullable', 'string', 'size:3', Rule::unique('countries', 'iso3')->ignore($country->id)],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
