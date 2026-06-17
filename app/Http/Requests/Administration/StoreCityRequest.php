<?php

namespace App\Http\Requests\Administration;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('city.create')
            || $this->user()?->can('campaign.create')
            || $this->user()?->can('campaign.update')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Country $country */
        $country = $this->route('country');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities', 'name')->where('country_id', $country->id),
            ],
            'name_ar' => ['nullable', 'string', 'max:255'],
        ];
    }
}
