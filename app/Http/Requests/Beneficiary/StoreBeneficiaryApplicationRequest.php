<?php

namespace App\Http\Requests\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBeneficiaryApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $extraTypes = array_map('intval', array_keys(config('beneficiary_registration.extra_types', [])));
        $namePattern = config('beneficiary_registration.name_pattern', '/^(([ء-ي]+)\s){2,8}([ء-ي]+)$/u');
        $mobilePattern = config('beneficiary_registration.mobile_pattern', '/^05\d{8}$/');
        $countries = array_keys(config('beneficiary_registration.countries', []));
        $maritalIds = array_map('intval', array_keys(config('beneficiary_registration.marital_status', [])));
        $diseaseIds = array_map('intval', array_keys(config('beneficiary_registration.diseases', [])));
        $diseaseTypeIds = array_map('intval', array_keys(config('beneficiary_registration.disease_types', [])));
        $treatmentTypeIds = array_map('intval', array_keys(config('beneficiary_registration.treatment_types', [])));

        return [
            'extra_type' => ['required', 'integer', Rule::in($extraTypes)],
            'name' => ['required', 'string', 'max:255', 'regex:'.$namePattern],
            'national_number' => ['required', 'digits:10'],
            'gender' => ['required', 'integer', Rule::in([1, 2])],
            'mobile' => ['required', 'string', 'regex:'.$mobilePattern],
            'country' => ['required', 'string', Rule::in($countries)],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'marital_status' => ['required', 'integer', Rule::in($maritalIds)],
            'photo' => ['nullable', 'image', 'max:5120'],

            'extra.case_description' => ['nullable', 'string', 'max:5000'],
            'extra.email' => ['nullable', 'email', 'max:255'],
            'extra.medical_reports' => ['nullable', 'array', 'max:10'],
            'extra.medical_reports.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx'],

            'health.health_status' => ['required', 'integer', Rule::in([1, 2])],
            'health.handicapped' => ['nullable', 'boolean'],
            'health.handicapped_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'health.diseases' => ['nullable', 'array'],
            'health.diseases.*.disease' => ['required', 'integer', Rule::in($diseaseIds)],
            'health.diseases.*.type' => ['required', 'integer', Rule::in($diseaseTypeIds)],
            'health.diseases.*.treatment_type' => ['required', 'integer', Rule::in($treatmentTypeIds)],
            'health.diseases.*.treatment_cost' => ['nullable', 'string', 'max:100'],
            'health.diseases.*.notes' => ['nullable', 'string', 'max:500'],
            'health.health_notes' => ['nullable', 'string', 'max:5000'],
            'health.health_medical_records' => ['nullable', 'array', 'max:10'],
            'health.health_medical_records.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx'],

            'profiles_national_images' => ['required', 'array', 'min:1', 'max:10'],
            'profiles_national_images.*' => ['required', 'image', 'max:5120'],
            'profiles_family_cards' => ['required', 'array', 'min:1', 'max:10'],
            'profiles_family_cards.*' => ['required', 'image', 'max:5120'],
            'profiles_attachments' => ['nullable', 'array', 'max:10'],
            'profiles_attachments.*' => ['file', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => __('beneficiary_registration.validation.name_arabic'),
            'national_number.digits' => __('beneficiary_registration.validation.national_exact'),
            'mobile.regex' => __('beneficiary_registration.validation.mobile_format'),
            'profiles_national_images.required' => __('beneficiary_registration.validation.files_required'),
            'profiles_family_cards.required' => __('beneficiary_registration.validation.files_required'),
            'health.diseases.*.disease.required' => __('beneficiary_registration.validation.disease_fields'),
            'health.diseases.*.type.required' => __('beneficiary_registration.validation.disease_fields'),
            'health.diseases.*.treatment_type.required' => __('beneficiary_registration.validation.disease_fields'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'health.diseases.*.disease' => __('beneficiary_registration.fields.disease'),
            'health.diseases.*.type' => __('beneficiary_registration.fields.disease_type'),
            'health.diseases.*.treatment_type' => __('beneficiary_registration.fields.treatment_type'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $extraType = (int) $this->input('extra_type');
            $extraFields = config("beneficiary_registration.extra_types.{$extraType}.extra_fields", []);

            if (isset($extraFields['case_description']) && ($extraFields['case_description']['required'] ?? false)) {
                if (! filled($this->input('extra.case_description'))) {
                    $validator->errors()->add(
                        'extra.case_description',
                        __('validation.required', ['attribute' => __('beneficiary_registration.fields.case_description')])
                    );
                }
            }

            if (isset($extraFields['email']) && ($extraFields['email']['required'] ?? false)) {
                if (! filled($this->input('extra.email'))) {
                    $validator->errors()->add(
                        'extra.email',
                        __('validation.required', ['attribute' => __('beneficiary_registration.fields.email')])
                    );
                }
            }

            if (isset($extraFields['medical_reports']) && ($extraFields['medical_reports']['required'] ?? false)) {
                $files = $this->file('extra.medical_reports', []);
                if (! is_array($files) || $files === []) {
                    $validator->errors()->add(
                        'extra.medical_reports',
                        __('beneficiary_registration.validation.files_required')
                    );
                }
            }

            if ((int) $this->input('health.health_status') === 2) {
                $diseases = $this->input('health.diseases', []);
                if (! is_array($diseases) || $diseases === []) {
                    $validator->errors()->add(
                        'health.diseases',
                        __('beneficiary_registration.validation.diseases_required')
                    );
                }

                if ($this->boolean('health.handicapped') && blank($this->input('health.handicapped_percentage'))) {
                    $validator->errors()->add(
                        'health.handicapped_percentage',
                        __('validation.required', ['attribute' => __('beneficiary_registration.fields.handicapped_percentage')])
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $health = $this->input('health', []);
        if (! is_array($health)) {
            $health = [];
        }

        $healthStatus = (int) ($health['health_status'] ?? 1);
        $diseases = is_array($health['diseases'] ?? null) ? $health['diseases'] : [];

        // Empty disease rows are always present in the wizard DOM; drop them.
        // When status is "healthy", ignore diseases entirely.
        if ($healthStatus !== 2) {
            $diseases = [];
            unset($health['handicapped'], $health['handicapped_percentage']);
        } else {
            $diseases = array_values(array_filter($diseases, function ($row): bool {
                if (! is_array($row)) {
                    return false;
                }

                return filled($row['disease'] ?? null)
                    || filled($row['type'] ?? null)
                    || filled($row['treatment_type'] ?? null)
                    || filled($row['treatment_cost'] ?? null)
                    || filled($row['notes'] ?? null);
            }));
        }

        $health['diseases'] = $diseases;
        $health['handicapped'] = $this->boolean('health.handicapped');

        $this->merge([
            'national_number' => preg_replace('/\D+/', '', (string) $this->input('national_number')),
            'mobile' => preg_replace('/\D+/', '', (string) $this->input('mobile')),
            'health' => $health,
        ]);
    }
}
