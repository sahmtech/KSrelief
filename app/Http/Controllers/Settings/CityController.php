<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreCityRequest;
use App\Http\Requests\Settings\UpdateCityRequest;
use App\Models\City;
use App\Models\Country;
use App\Services\Settings\CitySettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CityController extends Controller
{
    public function __construct(
        private readonly CitySettingService $citySettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', City::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'country_id' => $request->query('country_id'),
        ];

        return view('pages.settings.cities.index', [
            'cities' => $this->citySettingService->paginate(
                filled($filters['country_id']) ? (int) $filters['country_id'] : null,
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
            'countries' => Country::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', City::class);

        return view('pages.settings.cities.create', [
            'statuses' => SettingStatus::cases(),
            'countries' => Country::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        $this->citySettingService->create($request->validated());

        return redirect()
            ->route('settings.cities.index')
            ->with('success', __('settings.entities.cities.messages.created'));
    }

    public function show(City $city): View
    {
        $this->authorize('view', $city);

        $city->load(['country', 'creator', 'updater']);

        return view('pages.settings.cities.show', [
            'city' => $city,
        ]);
    }

    public function edit(City $city): View
    {
        $this->authorize('update', $city);

        return view('pages.settings.cities.edit', [
            'city' => $city,
            'statuses' => SettingStatus::cases(),
            'countries' => Country::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $this->citySettingService->update($city, $request->validated());

        return redirect()
            ->route('settings.cities.show', $city)
            ->with('success', __('settings.entities.cities.messages.updated'));
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->authorize('delete', $city);

        $this->citySettingService->delete($city);

        return redirect()
            ->route('settings.cities.index')
            ->with('success', __('settings.entities.cities.messages.deleted'));
    }
}
