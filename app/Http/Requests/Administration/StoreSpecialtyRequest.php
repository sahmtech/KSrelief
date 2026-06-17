<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;

class StoreSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($this->user()?->can('specialty.create')
            || $this->user()?->can('campaign.create')
            || $this->user()?->can('campaign.update')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:specialties,name'],
        ];
    }
}
