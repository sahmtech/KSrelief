<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\Specialty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpecialtyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Specialty $specialty */
        $specialty = $this->route('specialty');

        return $this->user()?->can('update', $specialty) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Specialty $specialty */
        $specialty = $this->route('specialty');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('specialties', 'code')->ignore($specialty->id)],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
