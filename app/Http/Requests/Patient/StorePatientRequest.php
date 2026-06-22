<?php

namespace App\Http\Requests\Patient;

use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Enums\PatientRecordStatus;
use App\Enums\SettingStatus;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Patient::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->patientRules();
    }

    /**
     * @return array<string, mixed>
     */
    protected function patientRules(): array
    {
        $campaignId = $this->input('campaign_id');

        return [
            'campaign_id' => ['required', 'integer', Rule::exists('campaigns', 'id')],
            'patient_name' => ['required', 'string', 'max:255'],
            'file_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('patients', 'file_number'),
            ],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(Gender::values())],
            'height_cm' => ['nullable', 'numeric', 'min:20', 'max:250'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:500'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'eligibility_status_id' => [
                'required',
                'integer',
                Rule::exists('patient_eligibility_statuses', 'id')->where('status', SettingStatus::Active->value),
            ],
            'current_stage_id' => [
                'nullable',
                'integer',
                Rule::exists('patient_stages', 'id')->where('status', SettingStatus::Active->value),
            ],
            'admission_status' => ['nullable', Rule::in(AdmissionStatus::values())],
            'surgery_day_number' => ['nullable', 'integer', 'min:1', 'max:99'],
            'rank' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'approval_reason' => ['nullable', 'string', 'max:10000'],
            'surgical_side' => ['nullable', Rule::in(['left', 'right', 'bilateral'])],
            'status' => ['nullable', Rule::in(PatientRecordStatus::values())],
            'notes' => ['nullable', 'string', 'max:10000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx', 'max:10240'],
            ...$this->screeningFieldRules(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function screeningFieldRules(): array
    {
        $rules = [];

        foreach (array_keys(config('patient_clinical.screening_fields', [])) as $key) {
            $rules['screening_'.$key] = ['nullable', 'string', 'max:20000'];
        }

        return $rules;
    }
}
