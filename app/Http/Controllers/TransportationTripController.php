<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transportation\AddPassengerRequest;
use App\Http\Requests\Transportation\ChangeTripStatusRequest;
use App\Http\Requests\Transportation\StoreTripRequest;
use App\Http\Requests\Transportation\UpdateTripRequest;
use App\Enums\TripStatus;
use App\Models\Campaign;
use App\Models\Member;
use App\Models\Patient;
use App\Models\TransportationTrip;
use App\Models\TransportationTripPassenger;
use App\Services\LookupService;
use App\Services\TransportationService;
use App\Services\TransportationStatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class TransportationTripController extends Controller
{
    public function __construct(
        private readonly TransportationService $transportationService,
        private readonly TransportationStatisticsService $statisticsService,
        private readonly LookupService $lookupService,
    ) {}

    public function index(Request $request): View
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

        $trips = TransportationTrip::query()
            ->with(['campaign', 'fromLocation', 'toLocation', 'creator'])
            ->withCount('passengers')
            ->search($filters['search'])
            ->filter($filters)
            ->orderByDesc('trip_date')
            ->orderByDesc('departure_time')
            ->get();

        return view('pages.transportation.index', [
            'trips' => $trips,
            'stats' => $this->statisticsService->getTripStats(
                $filters['campaign_id'] ? (int) $filters['campaign_id'] : null
            ),
            'filters' => $filters,
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'locations' => $this->lookupService->getTransportationLocations(limit: 200),
            'tripTypes' => \App\Enums\TripType::cases(),
            'tripStatuses' => TripStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TransportationTrip::class);

        return view('pages.transportation.create', $this->formData($request));
    }

    public function store(StoreTripRequest $request): RedirectResponse
    {
        $trip = $this->transportationService->createTrip($request->validated(), $request->user());

        return redirect()
            ->route('operations.transportation.show', $trip)
            ->with('success', __('transportation.messages.created'));
    }

    public function show(TransportationTrip $trip): View
    {
        $this->authorize('view', $trip);

        $trip->load([
            'campaign.country',
            'campaign.city',
            'fromLocation',
            'toLocation',
            'creator',
            'updater',
            'passengers.member.memberRole',
            'passengers.patient',
            'statusLogs.changedBy',
        ]);

        $campaignMembers = Member::query()
            ->with(['memberRole'])
            ->whereHas('campaignAssignments', fn ($q) => $q->where('campaign_id', $trip->campaign_id))
            ->orderBy('full_name')
            ->get();

        $campaignPatients = Patient::query()
            ->where('campaign_id', $trip->campaign_id)
            ->orderBy('patient_name')
            ->get(['id', 'patient_name', 'file_number']);

        $existingMemberIds = $trip->passengers->pluck('member_id')->filter()->all();
        $existingPatientIds = $trip->passengers->pluck('patient_id')->filter()->all();

        return view('pages.transportation.show', [
            'trip' => $trip,
            'campaignMembers' => $campaignMembers->whereNotIn('id', $existingMemberIds),
            'campaignPatients' => $campaignPatients->whereNotIn('id', $existingPatientIds),
            'statusTransitions' => $trip->status->allowedTransitions(),
        ]);
    }

    public function edit(TransportationTrip $trip): View
    {
        $this->authorize('update', $trip);

        $trip->load(['campaign', 'fromLocation', 'toLocation']);

        return view('pages.transportation.edit', array_merge(
            $this->formData(request()),
            ['trip' => $trip]
        ));
    }

    public function update(UpdateTripRequest $request, TransportationTrip $trip): RedirectResponse
    {
        try {
            $this->transportationService->updateTrip($trip, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['trip' => $e->getMessage()]);
        }

        return redirect()
            ->route('operations.transportation.show', $trip)
            ->with('success', __('transportation.messages.updated'));
    }

    public function destroy(TransportationTrip $trip): RedirectResponse
    {
        $this->authorize('delete', $trip);

        try {
            $this->transportationService->deleteTrip($trip);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['trip' => $e->getMessage()]);
        }

        return redirect()
            ->route('operations.transportation.index')
            ->with('success', __('transportation.messages.deleted'));
    }

    public function changeStatus(ChangeTripStatusRequest $request, TransportationTrip $trip): RedirectResponse
    {
        $status = TripStatus::from($request->validated('status'));

        try {
            $this->transportationService->changeStatus(
                $trip,
                $status,
                $request->user(),
                $request->validated('notes')
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', __('transportation.messages.status_changed'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        return [
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'locations' => $this->lookupService->getTransportationLocations(limit: 200),
            'tripTypes' => \App\Enums\TripType::cases(),
            'selectedCampaignId' => $request->integer('campaign_id') ?: null,
        ];
    }
}
