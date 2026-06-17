<?php

namespace App\Http\Resources;

use App\Models\TransportationTripPassenger;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TransportationTripPassenger
 */
class TransportationPassengerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trip_id' => $this->trip_id,
            'passenger_type' => $this->passenger_type?->value,
            'passenger_type_label' => $this->passengerTypeLabel(),
            'passenger_name' => $this->passengerName(),
            'member_id' => $this->member_id,
            'patient_id' => $this->patient_id,
            'notes' => $this->notes,
            'member' => $this->whenLoaded('member', fn () => [
                'id' => $this->member->id,
                'full_name' => $this->member->full_name,
                'role' => $this->member->memberRole?->name,
            ]),
            'patient' => $this->whenLoaded('patient', fn () => [
                'id' => $this->patient->id,
                'patient_name' => $this->patient->patient_name,
                'file_number' => $this->patient->file_number,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
