<?php

namespace App\Http\Requests\Transportation;

use App\Enums\PassengerType;
use App\Models\TransportationTripPassenger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPassengerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transportation.manage_passengers');
    }

    public function rules(): array
    {
        $trip = $this->route('trip');
        $campaignId = $trip?->campaign_id;

        return [
            'passenger_type' => ['required', Rule::enum(PassengerType::class)],
            'member_id' => [
                Rule::requiredIf(fn () => $this->input('passenger_type') === PassengerType::Member->value),
                'nullable',
                'integer',
                'exists:members,id',
                Rule::when(
                    $this->input('passenger_type') === PassengerType::Member->value && $campaignId,
                    Rule::exists('campaign_member', 'member_id')->where(
                        fn ($q) => $q->where('campaign_id', $campaignId)
                    )
                ),
            ],
            'patient_id' => [
                Rule::requiredIf(fn () => $this->input('passenger_type') === PassengerType::Patient->value),
                'nullable',
                'integer',
                'exists:patients,id',
                Rule::when(
                    $this->input('passenger_type') === PassengerType::Patient->value && $campaignId,
                    Rule::exists('patients', 'id')->where(
                        fn ($q) => $q->where('campaign_id', $campaignId)
                    )
                ),
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $trip = $this->route('trip');

            if (! $trip) {
                return;
            }

            if ($this->input('passenger_type') === PassengerType::Member->value && $this->filled('member_id')) {
                $exists = TransportationTripPassenger::query()
                    ->where('trip_id', $trip->id)
                    ->where('member_id', $this->input('member_id'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('member_id', __('transportation.errors.passenger_already_on_trip'));
                }
            }

            if ($this->input('passenger_type') === PassengerType::Patient->value && $this->filled('patient_id')) {
                $exists = TransportationTripPassenger::query()
                    ->where('trip_id', $trip->id)
                    ->where('patient_id', $this->input('patient_id'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('patient_id', __('transportation.errors.passenger_already_on_trip'));
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'member_id.exists' => __('transportation.errors.member_not_assigned'),
            'patient_id.exists' => __('transportation.errors.patient_not_in_campaign'),
        ];
    }
}
