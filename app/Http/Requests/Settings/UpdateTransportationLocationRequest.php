<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\TransportationLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransportationLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var TransportationLocation $transportationLocation */
        $transportationLocation = $this->route('transportation_location');

        return $this->user()?->can('update', $transportationLocation) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['hotel', 'hospital', 'airport', 'other'])],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
