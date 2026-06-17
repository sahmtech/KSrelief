<?php

namespace App\Http\Requests\Activity;

use App\Enums\ActivityParticipationStatus;
use App\Enums\PassengerType;
use App\Models\ActivityParticipant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('activity.manage_participants');
    }

    public function rules(): array
    {
        $activity = $this->route('activity');
        $campaignId = $activity?->campaign_id;

        return [
            'participant_type' => ['required', Rule::enum(PassengerType::class)],
            'member_id' => [
                Rule::requiredIf(fn () => $this->input('participant_type') === PassengerType::Member->value),
                'nullable', 'integer', 'exists:members,id',
                Rule::when(
                    $this->input('participant_type') === PassengerType::Member->value && $campaignId,
                    Rule::exists('campaign_member', 'member_id')->where(fn ($q) => $q->where('campaign_id', $campaignId))
                ),
            ],
            'patient_id' => [
                Rule::requiredIf(fn () => $this->input('participant_type') === PassengerType::Patient->value),
                'nullable', 'integer', 'exists:patients,id',
                Rule::when(
                    $this->input('participant_type') === PassengerType::Patient->value && $campaignId,
                    Rule::exists('patients', 'id')->where(fn ($q) => $q->where('campaign_id', $campaignId))
                ),
            ],
            'attendance_status' => ['nullable', Rule::enum(ActivityParticipationStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $activity = $this->route('activity');
            if (! $activity) {
                return;
            }

            if ($this->input('participant_type') === PassengerType::Member->value && $this->filled('member_id')) {
                if (ActivityParticipant::query()->where('activity_id', $activity->id)->where('member_id', $this->input('member_id'))->exists()) {
                    $validator->errors()->add('member_id', __('activities.errors.participant_already_registered'));
                }
            }

            if ($this->input('participant_type') === PassengerType::Patient->value && $this->filled('patient_id')) {
                if (ActivityParticipant::query()->where('activity_id', $activity->id)->where('patient_id', $this->input('patient_id'))->exists()) {
                    $validator->errors()->add('patient_id', __('activities.errors.participant_already_registered'));
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'member_id.exists' => __('activities.errors.member_not_assigned'),
            'patient_id.exists' => __('activities.errors.patient_not_in_campaign'),
        ];
    }
}
