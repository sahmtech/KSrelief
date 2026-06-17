<?php

namespace App\Http\Requests\Transportation;

use App\Enums\TripType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transportation.create');
    }

    public function rules(): array
    {
        return [
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
            'trip_date' => ['required', 'date'],
            'departure_time' => ['required', 'date_format:H:i'],
            'arrival_time' => ['nullable', 'date_format:H:i', 'after:departure_time'],
            'from_location_id' => ['required', 'integer', 'exists:transportation_locations,id'],
            'to_location_id' => ['required', 'integer', 'exists:transportation_locations,id', 'different:from_location_id'],
            'trip_type' => ['required', Rule::enum(TripType::class)],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
