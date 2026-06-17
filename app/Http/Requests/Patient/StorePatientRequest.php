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
                Rule::unique('patients', 'file_number')
                    ->where(fn ($query) => $query->where('campaign_id', $campaignId)),
            ],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(Gender::values())],
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
            'status' => ['nullable', Rule::in(PatientRecordStatus::values())],
            'notes' => ['nullable', 'string', 'max:10000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx', 'max:10240'],
        ];
    }
}
