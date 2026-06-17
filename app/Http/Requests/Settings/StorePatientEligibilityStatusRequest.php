<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\PatientEligibilityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientEligibilityStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PatientEligibilityStatus::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('patient_eligibility_statuses', 'code')],
            'color' => ['required', 'string', 'max:20'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
