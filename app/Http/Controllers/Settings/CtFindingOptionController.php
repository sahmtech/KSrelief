<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreCtFindingOptionRequest;
use App\Http\Requests\Settings\UpdateCtFindingOptionRequest;
use App\Models\CtFindingOption;
use App\Services\Settings\CtFindingOptionSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CtFindingOptionController extends Controller
{
    public function __construct(
        private readonly CtFindingOptionSettingService $ctFindingOptionSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CtFindingOption::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.ct-finding-options.index', [
            'options' => $this->ctFindingOptionSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CtFindingOption::class);

        return view('pages.settings.ct-finding-options.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreCtFindingOptionRequest $request): RedirectResponse
    {
        $this->ctFindingOptionSettingService->create($request->validated());

        return redirect()
            ->route('settings.ct-finding-options.index')
            ->with('success', __('settings.entities.ct_finding_options.messages.created'));
    }

    public function show(CtFindingOption $ctFindingOption): View
    {
        $this->authorize('view', $ctFindingOption);

        $ctFindingOption->load(['creator', 'updater']);

        return view('pages.settings.ct-finding-options.show', [
            'option' => $ctFindingOption,
        ]);
    }

    public function edit(CtFindingOption $ctFindingOption): View
    {
        $this->authorize('update', $ctFindingOption);

        return view('pages.settings.ct-finding-options.edit', [
            'option' => $ctFindingOption,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateCtFindingOptionRequest $request, CtFindingOption $ctFindingOption): RedirectResponse
    {
        $this->ctFindingOptionSettingService->update($ctFindingOption, $request->validated());

        return redirect()
            ->route('settings.ct-finding-options.show', $ctFindingOption)
            ->with('success', __('settings.entities.ct_finding_options.messages.updated'));
    }

    public function destroy(CtFindingOption $ctFindingOption): RedirectResponse
    {
        $this->authorize('delete', $ctFindingOption);

        $this->ctFindingOptionSettingService->delete($ctFindingOption);

        return redirect()
            ->route('settings.ct-finding-options.index')
            ->with('success', __('settings.entities.ct_finding_options.messages.deleted'));
    }
}
