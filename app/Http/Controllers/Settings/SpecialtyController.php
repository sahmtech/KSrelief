<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreSpecialtyRequest;
use App\Http\Requests\Settings\UpdateSpecialtyRequest;
use App\Models\Specialty;
use App\Services\Settings\SpecialtySettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialtyController extends Controller
{
    public function __construct(
        private readonly SpecialtySettingService $specialtySettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Specialty::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.specialties.index', [
            'specialties' => $this->specialtySettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Specialty::class);

        return view('pages.settings.specialties.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreSpecialtyRequest $request): RedirectResponse
    {
        $this->specialtySettingService->create($request->validated());

        return redirect()
            ->route('settings.specialties.index')
            ->with('success', __('settings.entities.specialties.messages.created'));
    }

    public function show(Specialty $specialty): View
    {
        $this->authorize('view', $specialty);

        $specialty->load(['creator', 'updater']);

        return view('pages.settings.specialties.show', [
            'specialty' => $specialty,
        ]);
    }

    public function edit(Specialty $specialty): View
    {
        $this->authorize('update', $specialty);

        return view('pages.settings.specialties.edit', [
            'specialty' => $specialty,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateSpecialtyRequest $request, Specialty $specialty): RedirectResponse
    {
        $this->specialtySettingService->update($specialty, $request->validated());

        return redirect()
            ->route('settings.specialties.show', $specialty)
            ->with('success', __('settings.entities.specialties.messages.updated'));
    }

    public function destroy(Specialty $specialty): RedirectResponse
    {
        $this->authorize('delete', $specialty);

        $this->specialtySettingService->delete($specialty);

        return redirect()
            ->route('settings.specialties.index')
            ->with('success', __('settings.entities.specialties.messages.deleted'));
    }
}
