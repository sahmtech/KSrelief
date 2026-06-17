<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreCampaignStatusRequest;
use App\Http\Requests\Settings\UpdateCampaignStatusRequest;
use App\Models\CampaignStatusRecord;
use App\Services\Settings\CampaignStatusSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignStatusController extends Controller
{
    public function __construct(
        private readonly CampaignStatusSettingService $campaignStatusSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CampaignStatusRecord::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.campaign-statuses.index', [
            'campaignStatuses' => $this->campaignStatusSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CampaignStatusRecord::class);

        return view('pages.settings.campaign-statuses.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StoreCampaignStatusRequest $request): RedirectResponse
    {
        $this->campaignStatusSettingService->create($request->validated());

        return redirect()
            ->route('settings.campaign-statuses.index')
            ->with('success', __('settings.entities.campaign_statuses.messages.created'));
    }

    public function show(CampaignStatusRecord $campaignStatus): View
    {
        $this->authorize('view', $campaignStatus);

        $campaignStatus->load(['creator', 'updater']);

        return view('pages.settings.campaign-statuses.show', [
            'campaignStatus' => $campaignStatus,
        ]);
    }

    public function edit(CampaignStatusRecord $campaignStatus): View
    {
        $this->authorize('update', $campaignStatus);

        return view('pages.settings.campaign-statuses.edit', [
            'campaignStatus' => $campaignStatus,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdateCampaignStatusRequest $request, CampaignStatusRecord $campaignStatus): RedirectResponse
    {
        $this->campaignStatusSettingService->update($campaignStatus, $request->validated());

        return redirect()
            ->route('settings.campaign-statuses.show', $campaignStatus)
            ->with('success', __('settings.entities.campaign_statuses.messages.updated'));
    }

    public function destroy(CampaignStatusRecord $campaignStatus): RedirectResponse
    {
        $this->authorize('delete', $campaignStatus);

        $this->campaignStatusSettingService->delete($campaignStatus);

        return redirect()
            ->route('settings.campaign-statuses.index')
            ->with('success', __('settings.entities.campaign_statuses.messages.deleted'));
    }
}
