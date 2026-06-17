<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreCountryRequest;
use App\Http\Requests\Settings\UpdateCountryRequest;
use App\Models\Country;
use App\Services\Settings\CountrySettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function __construct(
        private readonly CountrySettingService $countrySettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Country::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.countries.index', [
            'countries' => $this->countrySettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Country::class);

        return view('pages.settings.countries.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreCountryRequest $request): RedirectResponse
    {
        $this->countrySettingService->create($request->validated());

        return redirect()
            ->route('settings.countries.index')
            ->with('success', __('settings.entities.countries.messages.created'));
    }

    public function show(Country $country): View
    {
        $this->authorize('view', $country);

        $country->load(['creator', 'updater']);

        return view('pages.settings.countries.show', [
            'country' => $country,
        ]);
    }

    public function edit(Country $country): View
    {
        $this->authorize('update', $country);

        return view('pages.settings.countries.edit', [
            'country' => $country,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateCountryRequest $request, Country $country): RedirectResponse
    {
        $this->countrySettingService->update($country, $request->validated());

        return redirect()
            ->route('settings.countries.show', $country)
            ->with('success', __('settings.entities.countries.messages.updated'));
    }

    public function destroy(Country $country): RedirectResponse
    {
        $this->authorize('delete', $country);

        $this->countrySettingService->delete($country);

        return redirect()
            ->route('settings.countries.index')
            ->with('success', __('settings.entities.countries.messages.deleted'));
    }
}
