<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreActivityTypeRequest;
use App\Http\Requests\Settings\UpdateActivityTypeRequest;
use App\Models\ActivityType;
use App\Services\Settings\ActivityTypeSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityTypeController extends Controller
{
    public function __construct(
        private readonly ActivityTypeSettingService $activityTypeSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ActivityType::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.activity-types.index', [
            'activityTypes' => $this->activityTypeSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ActivityType::class);

        return view('pages.settings.activity-types.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreActivityTypeRequest $request): RedirectResponse
    {
        $this->activityTypeSettingService->create($request->validated());

        return redirect()
            ->route('settings.activity-types.index')
            ->with('success', __('settings.entities.activity_types.messages.created'));
    }

    public function show(ActivityType $activityType): View
    {
        $this->authorize('view', $activityType);

        $activityType->load(['creator', 'updater']);

        return view('pages.settings.activity-types.show', [
            'activityType' => $activityType,
        ]);
    }

    public function edit(ActivityType $activityType): View
    {
        $this->authorize('update', $activityType);

        return view('pages.settings.activity-types.edit', [
            'activityType' => $activityType,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateActivityTypeRequest $request, ActivityType $activityType): RedirectResponse
    {
        $this->activityTypeSettingService->update($activityType, $request->validated());

        return redirect()
            ->route('settings.activity-types.show', $activityType)
            ->with('success', __('settings.entities.activity_types.messages.updated'));
    }

    public function destroy(ActivityType $activityType): RedirectResponse
    {
        $this->authorize('delete', $activityType);

        $this->activityTypeSettingService->delete($activityType);

        return redirect()
            ->route('settings.activity-types.index')
            ->with('success', __('settings.entities.activity_types.messages.deleted'));
    }
}
