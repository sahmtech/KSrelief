<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreTransportationLocationRequest;
use App\Http\Requests\Settings\UpdateTransportationLocationRequest;
use App\Models\TransportationLocation;
use App\Services\Settings\TransportationLocationSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportationLocationController extends Controller
{
    public function __construct(
        private readonly TransportationLocationSettingService $transportationLocationSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TransportationLocation::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'type' => $request->query('type'),
        ];

        return view('pages.settings.transportation-locations.index', [
            'transportationLocations' => $this->transportationLocationSettingService->paginate(
                $filters['search'],
                $filters['status'],
                $filters['type']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
            'types' => ['hotel', 'hospital', 'airport', 'other'],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TransportationLocation::class);

        return view('pages.settings.transportation-locations.create', [
            'statuses' => SettingStatus::cases(),
            'types' => ['hotel', 'hospital', 'airport', 'other'],
        ]);
    }

    public function store(StoreTransportationLocationRequest $request): RedirectResponse
    {
        $this->transportationLocationSettingService->create($request->validated());

        return redirect()
            ->route('settings.transportation-locations.index')
            ->with('success', __('settings.entities.transportation_locations.messages.created'));
    }

    public function show(TransportationLocation $transportationLocation): View
    {
        $this->authorize('view', $transportationLocation);

        $transportationLocation->load(['creator', 'updater']);

        return view('pages.settings.transportation-locations.show', [
            'transportationLocation' => $transportationLocation,
        ]);
    }

    public function edit(TransportationLocation $transportationLocation): View
    {
        $this->authorize('update', $transportationLocation);

        return view('pages.settings.transportation-locations.edit', [
            'transportationLocation' => $transportationLocation,
            'statuses' => SettingStatus::cases(),
            'types' => ['hotel', 'hospital', 'airport', 'other'],
        ]);
    }

    public function update(UpdateTransportationLocationRequest $request, TransportationLocation $transportationLocation): RedirectResponse
    {
        $this->transportationLocationSettingService->update($transportationLocation, $request->validated());

        return redirect()
            ->route('settings.transportation-locations.show', $transportationLocation)
            ->with('success', __('settings.entities.transportation_locations.messages.updated'));
    }

    public function destroy(TransportationLocation $transportationLocation): RedirectResponse
    {
        $this->authorize('delete', $transportationLocation);

        $this->transportationLocationSettingService->delete($transportationLocation);

        return redirect()
            ->route('settings.transportation-locations.index')
            ->with('success', __('settings.entities.transportation_locations.messages.deleted'));
    }
}
