<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingStatus;
use App\Models\PatientEligibilityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientEligibilityStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var PatientEligibilityStatus $patientEligibilityStatus */
        $patientEligibilityStatus = $this->route('patient_eligibility_status');

        return $this->user()?->can('update', $patientEligibilityStatus) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var PatientEligibilityStatus $patientEligibilityStatus */
        $patientEligibilityStatus = $this->route('patient_eligibility_status');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('patient_eligibility_statuses', 'code')->ignore($patientEligibilityStatus->id),
            ],
            'color' => ['required', 'string', 'max:20'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(SettingStatus::values())],
        ];
    }
}
