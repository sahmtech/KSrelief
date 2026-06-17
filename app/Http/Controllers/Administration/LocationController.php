<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreCityRequest;
use App\Models\Country;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function __construct(
        private readonly LocationService $locationService
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeSettings();

        $countries = Country::query()
            ->active()
            ->search($request->query('search'))
            ->withCount('cities')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('pages.administration.locations.index', [
            'countries' => $countries,
            'search' => $request->query('search'),
        ]);
    }

    public function show(Country $country, Request $request): View
    {
        $this->authorizeSettings();

        $country->loadCount('cities');

        $cities = $country->cities()
            ->active()
            ->search($request->query('search'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('pages.administration.locations.show', [
            'country' => $country,
            'cities' => $cities,
            'search' => $request->query('search'),
        ]);
    }

    public function countries(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('campaign.view'), 403);

        $countries = $this->locationService->searchCountries(
            $request->query('q'),
            (int) $request->query('limit', 100)
        );

        return response()->json([
            'data' => $countries->map(fn (Country $country) => [
                'id' => $country->id,
                'name' => $country->localizedName(),
                'code' => $country->code,
            ]),
        ]);
    }

    public function cities(Country $country, Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('campaign.view'), 403);

        $cities = $this->locationService->citiesForCountry(
            $country,
            $request->query('q'),
            (int) $request->query('limit', 100)
        );

        return response()->json([
            'data' => $cities->map(fn ($city) => [
                'id' => $city->id,
                'name' => $city->localizedName(),
            ]),
        ]);
    }

    public function storeCity(StoreCityRequest $request, Country $country): JsonResponse
    {
        $city = $this->locationService->createCity(
            $country,
            $request->validated('name'),
            $request->validated('name_ar')
        );

        return response()->json([
            'message' => __('locations.messages.city_created'),
            'data' => [
                'id' => $city->id,
                'name' => $city->localizedName(),
            ],
        ], 201);
    }

    private function authorizeSettings(): void
    {
        abort_unless(auth()->user()?->can('settings.update'), 403);
    }
}
