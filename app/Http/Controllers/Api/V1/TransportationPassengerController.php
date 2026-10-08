<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\InteractsWithCampaignAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transportation\AddPassengerRequest;
use App\Http\Resources\TransportationPassengerResource;
use App\Models\TransportationTrip;
use App\Models\TransportationTripPassenger;
use App\Services\TransportationService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class TransportationPassengerController extends Controller
{
    use InteractsWithCampaignAccess;

    public function __construct(
        private readonly TransportationService $transportationService,
    ) {}

    public function store(AddPassengerRequest $request, TransportationTrip $trip): JsonResponse
    {
        $this->authorize('managePassengers', $trip);
        $this->assertModelCampaignAccessible($trip);

        try {
            $passenger = $this->transportationService->addPassenger($trip, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $passenger->load(['member.memberRole', 'patient']);

        return response()->json([
            'message' => __('transportation.messages.passenger_added'),
            'data' => TransportationPassengerResource::make($passenger),
        ], 201);
    }

    public function destroy(TransportationTrip $trip, TransportationTripPassenger $passenger): JsonResponse
    {
        $this->authorize('managePassengers', $trip);
        $this->assertModelCampaignAccessible($trip);

        abort_unless($passenger->trip_id === $trip->id, 404);

        try {
            $this->transportationService->removePassenger($passenger);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => __('transportation.messages.passenger_removed'),
        ]);
    }
}
