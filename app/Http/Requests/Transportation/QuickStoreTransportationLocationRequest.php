<?php

namespace App\Http\Requests\Transportation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickStoreTransportationLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->can('transport_location.create')
            || $user?->can('transportation.create');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['hotel', 'hospital', 'airport', 'other'])],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
