<?php

namespace App\Http\Resources;

use App\Models\TransportationTrip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TransportationTrip
 */
class TransportationTripResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'trip_code' => $this->trip_code,
            'trip_date' => $this->trip_date?->toDateString(),
            'departure_time' => $this->departureTimeLabel(),
            'arrival_time' => $this->arrivalTimeLabel(),
            'from_location_id' => $this->from_location_id,
            'to_location_id' => $this->to_location_id,
            'trip_type' => $this->trip_type?->value,
            'trip_type_label' => $this->tripTypeLabel(),
            'vehicle_number' => $this->vehicle_number,
            'driver_name' => $this->driver_name,
            'capacity' => $this->capacity,
            'notes' => $this->notes,
            'status' => $this->status?->value,
            'status_label' => $this->statusLabel(),
            'passengers_count' => $this->passengersCount(),
            'campaign' => $this->whenLoaded('campaign', fn () => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
            ]),
            'from_location' => $this->whenLoaded('fromLocation', fn () => [
                'id' => $this->fromLocation->id,
                'name' => $this->fromLocation->name,
                'type' => $this->fromLocation->type,
            ]),
            'to_location' => $this->whenLoaded('toLocation', fn () => [
                'id' => $this->toLocation->id,
                'name' => $this->toLocation->name,
                'type' => $this->toLocation->type,
            ]),
            'passengers' => TransportationPassengerResource::collection($this->whenLoaded('passengers')),
            'created_by' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
