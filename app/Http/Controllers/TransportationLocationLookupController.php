<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transportation\QuickStoreTransportationLocationRequest;
use App\Services\LookupService;
use App\Services\Settings\TransportationLocationSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransportationLocationLookupController extends Controller
{
    public function __construct(
        private readonly LookupService $lookupService,
        private readonly TransportationLocationSettingService $transportationLocationSettingService,
    ) {}

    public function search(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('transportation.view'), 403);

        $locations = $this->lookupService->getTransportationLocations(
            term: $request->query('q'),
            limit: (int) $request->query('limit', 50),
        );

        return response()->json([
            'data' => $locations->map(fn ($location) => $this->locationPayload($location))->values(),
        ]);
    }

    public function store(QuickStoreTransportationLocationRequest $request): JsonResponse
    {
        $location = $this->transportationLocationSettingService->create([
            'name' => $request->validated('name'),
            'type' => $request->validated('type'),
            'description' => $request->validated('description'),
            'status' => 'active',
        ], $request->user()?->id);

        return response()->json([
            'data' => $this->locationPayload($location),
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function locationPayload(object $location): array
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'type' => $location->type,
            'label' => $location->name.' ('.__('settings.transportation_types.'.$location->type).')',
        ];
    }
}
