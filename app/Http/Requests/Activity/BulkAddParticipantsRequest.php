<?php

namespace App\Http\Requests\Activity;

use App\Enums\PassengerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAddParticipantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activity.manage_participants');
    }

    public function rules(): array
    {
        return [
            'rows' => ['sometimes', 'array', 'min:1'],
            'rows.*.participant_type' => ['required_with:rows', Rule::enum(PassengerType::class)],
            'rows.*.member_id' => ['nullable', 'integer', 'exists:members,id'],
            'rows.*.patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'rows.*.notes' => ['nullable', 'string', 'max:1000'],
            'patient_ids' => ['sometimes', 'array'],
            'patient_ids.*' => ['integer', 'exists:patients,id'],
            'member_ids' => ['sometimes', 'array'],
            'member_ids.*' => ['integer', 'exists:members,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $patientIds = collect($this->input('patient_ids', []))->filter(fn ($id) => filled($id));
            $memberIds = collect($this->input('member_ids', []))->filter(fn ($id) => filled($id));
            $hasRows = is_array($this->input('rows')) && count($this->input('rows')) > 0;

            if (! $hasRows && $patientIds->isEmpty() && $memberIds->isEmpty()) {
                $validator->errors()->add('participants', __('activities.errors.no_participants_selected'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'patient_ids' => collect($this->input('patient_ids', []))
                ->filter(fn ($id) => filled($id))
                ->values()
                ->all(),
            'member_ids' => collect($this->input('member_ids', []))
                ->filter(fn ($id) => filled($id))
                ->values()
                ->all(),
        ]);
    }
}
