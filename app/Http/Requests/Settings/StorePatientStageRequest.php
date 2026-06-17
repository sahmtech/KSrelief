<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\PatientStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PatientStage::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('patient_stages', 'code')],
            'description' => ['nullable', 'string'],
            'color' => ['required', 'string', 'max:20'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
