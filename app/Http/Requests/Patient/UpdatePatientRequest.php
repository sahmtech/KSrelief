<?php

namespace App\Http\Requests\Patient;

use App\Models\Patient;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends StorePatientRequest
{
    public function authorize(): bool
    {
        $patient = $this->route('patient');

        return $patient instanceof Patient
            && ($this->user()?->can('update', $patient) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $patient = $this->route('patient');
        $campaignId = $this->input('campaign_id', $patient?->campaign_id);

        return [
            ...parent::patientRules(),
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
            'file_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('patients', 'file_number')->ignore($patient?->id),
            ],
        ];
    }
}
