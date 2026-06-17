<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\TransportationLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransportationLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TransportationLocation::class) ?? false;
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
