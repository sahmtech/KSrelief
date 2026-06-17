<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Models\Campaign;
use App\Services\DashboardService;
use App\Support\DashboardFilter;
use App\Support\DashboardScopeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly DashboardScopeResolver $dashboardScopeResolver,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewDashboard');

        $user = $request->user();
        $scopedIds = $this->dashboardScopeResolver->scopedCampaignIds($user);
        $filter = DashboardFilter::fromRequest($request, $scopedIds);
        $dashboard = $this->dashboardService->getExecutiveDashboard($user, $filter);

        return view('dashboard.index', compact('dashboard', 'filter'));
    }

    public function campaignDashboard(Request $request, Campaign $campaign): View
    {
        $this->authorize('viewCampaignDashboard', $campaign);

        $dashboard = $this->dashboardService->getCampaignDashboard($request->user(), $campaign);

        return view('dashboard.campaign', [
            'dashboard' => $dashboard,
            'campaign' => $campaign,
            'filter' => $dashboard['filter'],
        ]);
    }

    public function api(Request $request): JsonResponse
    {
        $this->authorize('viewDashboard');

        $user = $request->user();
        $filter = DashboardFilter::fromRequest($request, $this->dashboardScopeResolver->scopedCampaignIds($user));
        $dashboard = $this->dashboardService->getExecutiveDashboard($user, $filter);

        return DashboardResource::make($dashboard)->response();
    }
}
