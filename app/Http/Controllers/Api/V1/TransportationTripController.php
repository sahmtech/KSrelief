<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TripStatus;
use App\Http\Controllers\Api\V1\Concerns\InteractsWithCampaignAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transportation\ChangeTripStatusRequest;
use App\Http\Requests\Transportation\StoreTripRequest;
use App\Http\Requests\Transportation\UpdateTripRequest;
use App\Http\Resources\TransportationTripResource;
use App\Models\Member;
use App\Models\Patient;
use App\Models\TransportationTrip;
use App\Services\PatientAccessService;
use App\Services\TransportationService;
use App\Services\TransportationStatisticsService;
use App\Support\ApiOperationsPermissions;
use App\Support\PatientFileNumberStyleSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TransportationTripController extends Controller
{
    use InteractsWithCampaignAccess;

    public function __construct(
        private readonly TransportationService $transportationService,
        private readonly TransportationStatisticsService $statisticsService,
        private readonly PatientAccessService $campaignAccess,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TransportationTrip::class);

        $filters = [
            'search' => $request->query('search'),
            'campaign_id' => $request->query('campaign_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'trip_type' => $request->query('trip_type'),
            'status' => $request->query('status'),
            'from_location_id' => $request->query('from_location_id'),
            'to_location_id' => $request->query('to_location_id'),
        ];

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = TransportationTrip::query()
            ->with(['campaign', 'fromLocation', 'toLocation', 'creator'])
            ->withCount('passengers');

        $this->campaignAccess->scopeVisibleByCampaign($query, $request->user());

        $trips = $query
            ->search($filters['search'])
            ->filter($filters)
            ->orderByDesc('trip_date')
            ->orderByDesc('departure_time')
            ->paginate($perPage);

        return response()->json([
            'data' => TransportationTripResource::collection($trips),
            'meta' => [
                'current_page' => $trips->currentPage(),
                'last_page' => $trips->lastPage(),
                'per_page' => $trips->perPage(),
                'total' => $trips->total(),
            ],
            'filters' => array_filter($filters, fn ($value) => filled($value)),
            'stats' => $this->statisticsService->getTripStats(
                $filters['campaign_id'] ? (int) $filters['campaign_id'] : null
            ),
            'permissions' => ApiOperationsPermissions::forTrip($request->user()),
        ]);
    }

    public function store(StoreTripRequest $request): JsonResponse
    {
        $this->assertCampaignAccessible((int) $request->validated('campaign_id'));

        $trip = $this->transportationService->createTrip($request->validated(), $request->user());
        $trip->load(['campaign', 'fromLocation', 'toLocation', 'creator']);

        return response()->json([
            'message' => __('transportation.messages.created'),
            'data' => TransportationTripResource::make($trip),
        ], 201);
    }

    public function show(Request $request, TransportationTrip $trip): JsonResponse
    {
        $this->authorize('view', $trip);
        $this->assertModelCampaignAccessible($trip);

        $trip->load([
            'campaign.country',
            'fromLocation',
            'toLocation',
            'creator',
            'updater',
            'passengers.member.memberRole',
            'passengers.patient',
        ]);

        return response()->json([
            'data' => TransportationTripResource::make($trip),
            'status_transitions' => collect($trip->status->allowedTransitions())->map(fn (TripStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->values(),
            'permissions' => ApiOperationsPermissions::forTrip($request->user(), $trip),
        ]);
    }

    public function passengerOptions(Request $request, TransportationTrip $trip): JsonResponse
    {
        $this->authorize('managePassengers', $trip);
        $this->assertModelCampaignAccessible($trip);

        $campaignMembers = Member::query()
            ->with(['memberRole'])
            ->whereHas('campaignAssignments', fn ($q) => $q->where('campaign_id', $trip->campaign_id))
            ->orderBy('full_name')
            ->get();

        $campaignPatients = Patient::query()
            ->where('campaign_id', $trip->campaign_id)
            ->orderBy('patient_name')
            ->get(['id', 'patient_name', 'file_number']);

        PatientFileNumberStyleSupport::applyAccentColors($campaignPatients);

        $existingMemberIds = $trip->passengers()->pluck('member_id')->filter()->all();
        $existingPatientIds = $trip->passengers()->pluck('patient_id')->filter()->all();

        return response()->json([
            'members' => $campaignMembers->whereNotIn('id', $existingMemberIds)->values()->map(fn (Member $m) => [
                'id' => $m->id,
                'full_name' => $m->full_name,
                'role' => $m->memberRole?->name,
            ]),
            'patients' => $campaignPatients->whereNotIn('id', $existingPatientIds)->values(),
        ]);
    }

    public function update(UpdateTripRequest $request, TransportationTrip $trip): JsonResponse
    {
        $this->authorize('update', $trip);
        $this->assertModelCampaignAccessible($trip);

        if ($request->filled('campaign_id')) {
            $this->assertCampaignAccessible((int) $request->validated('campaign_id'));
        }

        try {
            $this->transportationService->updateTrip($trip, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $trip->refresh()->load(['campaign', 'fromLocation', 'toLocation', 'creator']);

        return response()->json([
            'message' => __('transportation.messages.updated'),
            'data' => TransportationTripResource::make($trip),
        ]);
    }

    public function changeStatus(ChangeTripStatusRequest $request, TransportationTrip $trip): JsonResponse
    {
        $this->authorize('changeStatus', $trip);
        $this->assertModelCampaignAccessible($trip);

        $status = TripStatus::from($request->validated('status'));

        try {
            $this->transportationService->changeStatus(
                $trip,
                $status,
                $request->user(),
                $request->validated('notes')
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $trip->refresh()->load(['campaign', 'fromLocation', 'toLocation', 'creator']);

        return response()->json([
            'message' => __('transportation.messages.status_changed'),
            'data' => TransportationTripResource::make($trip),
        ]);
    }

    public function destroy(Request $request, TransportationTrip $trip): JsonResponse
    {
        $this->authorize('delete', $trip);
        $this->assertModelCampaignAccessible($trip);

        try {
            $this->transportationService->deleteTrip($trip);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => __('transportation.messages.deleted'),
        ]);
    }
}
