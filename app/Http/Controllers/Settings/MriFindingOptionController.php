<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreMriFindingOptionRequest;
use App\Http\Requests\Settings\UpdateMriFindingOptionRequest;
use App\Models\MriFindingOption;
use App\Services\Settings\MriFindingOptionSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MriFindingOptionController extends Controller
{
    public function __construct(
        private readonly MriFindingOptionSettingService $mriFindingOptionSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MriFindingOption::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.mri-finding-options.index', [
            'options' => $this->mriFindingOptionSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MriFindingOption::class);

        return view('pages.settings.mri-finding-options.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreMriFindingOptionRequest $request): RedirectResponse
    {
        $this->mriFindingOptionSettingService->create($request->validated());

        return redirect()
            ->route('settings.mri-finding-options.index')
            ->with('success', __('settings.entities.mri_finding_options.messages.created'));
    }

    public function show(MriFindingOption $mriFindingOption): View
    {
        $this->authorize('view', $mriFindingOption);

        $mriFindingOption->load(['creator', 'updater']);

        return view('pages.settings.mri-finding-options.show', [
            'option' => $mriFindingOption,
        ]);
    }

    public function edit(MriFindingOption $mriFindingOption): View
    {
        $this->authorize('update', $mriFindingOption);

        return view('pages.settings.mri-finding-options.edit', [
            'option' => $mriFindingOption,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateMriFindingOptionRequest $request, MriFindingOption $mriFindingOption): RedirectResponse
    {
        $this->mriFindingOptionSettingService->update($mriFindingOption, $request->validated());

        return redirect()
            ->route('settings.mri-finding-options.show', $mriFindingOption)
            ->with('success', __('settings.entities.mri_finding_options.messages.updated'));
    }

    public function destroy(MriFindingOption $mriFindingOption): RedirectResponse
    {
        $this->authorize('delete', $mriFindingOption);

        $this->mriFindingOptionSettingService->delete($mriFindingOption);

        return redirect()
            ->route('settings.mri-finding-options.index')
            ->with('success', __('settings.entities.mri_finding_options.messages.deleted'));
    }
}
