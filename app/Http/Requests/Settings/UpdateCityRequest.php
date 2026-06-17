<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var City $city */
        $city = $this->route('city');

        return $this->user()?->can('update', $city) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var City $city */
        $city = $this->route('city');

        return [
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities', 'name')
                    ->where('country_id', $this->input('country_id'))
                    ->ignore($city->id),
            ],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
