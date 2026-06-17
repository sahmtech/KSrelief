<?php

namespace App\Http\Requests\Transportation;

use App\Enums\TripType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transportation.update');
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['sometimes', 'required', 'integer', 'exists:campaigns,id'],
            'trip_date' => ['sometimes', 'required', 'date'],
            'departure_time' => ['sometimes', 'required', 'date_format:H:i'],
            'arrival_time' => ['nullable', 'date_format:H:i'],
            'from_location_id' => ['sometimes', 'required', 'integer', 'exists:transportation_locations,id'],
            'to_location_id' => ['sometimes', 'required', 'integer', 'exists:transportation_locations,id', 'different:from_location_id'],
            'trip_type' => ['sometimes', 'required', Rule::enum(TripType::class)],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
