<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transportation\AddPassengerRequest;
use App\Models\TransportationTrip;
use App\Models\TransportationTripPassenger;
use App\Services\TransportationService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class TransportationPassengerController extends Controller
{
    public function __construct(
        private readonly TransportationService $transportationService,
    ) {}

    public function store(AddPassengerRequest $request, TransportationTrip $trip): RedirectResponse
    {
        $this->authorize('managePassengers', $trip);

        try {
            $this->transportationService->addPassenger($trip, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['passenger' => $e->getMessage()]);
        }

        return back()->with('success', __('transportation.messages.passenger_added'));
    }

    public function destroy(TransportationTrip $trip, TransportationTripPassenger $passenger): RedirectResponse
    {
        $this->authorize('managePassengers', $trip);

        abort_unless($passenger->trip_id === $trip->id, 404);

        try {
            $this->transportationService->removePassenger($passenger);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['passenger' => $e->getMessage()]);
        }

        return back()->with('success', __('transportation.messages.passenger_removed'));
    }
}
