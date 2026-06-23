<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreInsertionApproachRequest;
use App\Http\Requests\Settings\UpdateInsertionApproachRequest;
use App\Models\InsertionApproach;
use App\Services\Settings\InsertionApproachSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsertionApproachController extends Controller
{
    public function __construct(
        private readonly InsertionApproachSettingService $insertionApproachSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', InsertionApproach::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.insertion-approaches.index', [
            'approaches' => $this->insertionApproachSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', InsertionApproach::class);

        return view('pages.settings.insertion-approaches.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreInsertionApproachRequest $request): RedirectResponse
    {
        $this->insertionApproachSettingService->create($request->validated());

        return redirect()
            ->route('settings.insertion-approaches.index')
            ->with('success', __('settings.entities.insertion_approaches.messages.created'));
    }

    public function show(InsertionApproach $insertionApproach): View
    {
        $this->authorize('view', $insertionApproach);

        $insertionApproach->load(['creator', 'updater']);

        return view('pages.settings.insertion-approaches.show', [
            'approach' => $insertionApproach,
        ]);
    }

    public function edit(InsertionApproach $insertionApproach): View
    {
        $this->authorize('update', $insertionApproach);

        return view('pages.settings.insertion-approaches.edit', [
            'approach' => $insertionApproach,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateInsertionApproachRequest $request, InsertionApproach $insertionApproach): RedirectResponse
    {
        $this->insertionApproachSettingService->update($insertionApproach, $request->validated());

        return redirect()
            ->route('settings.insertion-approaches.show', $insertionApproach)
            ->with('success', __('settings.entities.insertion_approaches.messages.updated'));
    }

    public function destroy(InsertionApproach $insertionApproach): RedirectResponse
    {
        $this->authorize('delete', $insertionApproach);

        $this->insertionApproachSettingService->delete($insertionApproach);

        return redirect()
            ->route('settings.insertion-approaches.index')
            ->with('success', __('settings.entities.insertion_approaches.messages.deleted'));
    }
}
