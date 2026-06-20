<?php

namespace App\Http\Controllers;

use App\Http\Requests\Campaign\ChangeCampaignStatusRequest;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Http\Requests\Member\AssignMemberToCampaignRequest;
use App\Models\Campaign;
use App\Models\City;
use App\Models\Country;
use App\Models\Member;
use App\Models\Patient;
use App\Models\Specialty;
use App\Services\ActivityStatisticsService;
use App\Services\AttendanceStatisticsService;
use App\Services\CampaignDailyBreakdownService;
use App\Services\CampaignService;
use App\Services\LookupService;
use App\Services\MemberService;
use App\Services\PatientStatisticsService;
use App\Services\TransportationStatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        private readonly CampaignService $campaignService,
        private readonly LookupService $lookupService,
        private readonly MemberService $memberService,
        private readonly PatientStatisticsService $patientStatisticsService,
        private readonly AttendanceStatisticsService $attendanceStatisticsService,
        private readonly TransportationStatisticsService $transportationStatisticsService,
        private readonly ActivityStatisticsService $activityStatisticsService,
        private readonly CampaignDailyBreakdownService $campaignDailyBreakdownService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Campaign::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'country_id' => $request->query('country_id'),
            'city_id' => $request->query('city_id'),
            'specialty_id' => $request->query('specialty_id'),
            'start_from' => $request->query('start_from'),
            'end_to' => $request->query('end_to'),
            'sort' => $request->query('sort'),
            'direction' => $request->query('direction'),
        ];

        $campaigns = Campaign::query()
            ->with(['country', 'city', 'specialty', 'campaignStatus', 'creator'])
            ->search($filters['search'])
            ->filter($filters)
            ->sortable($filters['sort'], $filters['direction'])
            ->paginate(15)
            ->withQueryString();

        $filterCountry = filled($filters['country_id'])
            ? Country::query()->find($filters['country_id'])
            : null;
        $filterCity = filled($filters['city_id'])
            ? City::query()->find($filters['city_id'])
            : null;

        return view('pages.campaigns.index', [
            'campaigns' => $campaigns,
            'stats' => $this->campaignService->getDashboardStats(),
            'filters' => $filters,
            'filterCountry' => $filterCountry,
            'filterCity' => $filterCity,
            'specialties' => $this->lookupService->getSpecialties(),
            'statuses' => $this->lookupService->getCampaignStatuses(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Campaign::class);

        return view('pages.campaigns.create', $this->formData());
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $campaign = $this->campaignService->createCampaign(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', __('campaigns.messages.created'));
    }

    public function show(Campaign $campaign): View
    {
        $this->authorize('view', $campaign);

        $campaign->load([
            'country',
            'city',
            'specialty',
            'campaignStatus',
            'creator',
            'updater',
            'campaignMemberAssignments.member.memberRole',
            'campaignMemberAssignments.member.specialty',
            'campaignMemberAssignments.creator',
        ]);

        $assignedMemberIds = $campaign->campaignMemberAssignments->pluck('member_id');
        $patientStats = $this->patientStatisticsService->getPatientCounts($campaign->id);
        $stageStats   = $this->patientStatisticsService->getStageStats($campaign->id);
        $attendanceStats = $this->attendanceStatisticsService->getCampaignStats($campaign->id);
        $recentAttendances = $this->attendanceStatisticsService->getRecentAttendances(10, $campaign->id);
        $attendanceCount = $attendanceStats['total'];
        $transportStats = $this->transportationStatisticsService->getCampaignTripStats($campaign->id);
        $recentTrips = $this->transportationStatisticsService->getRecentTrips(10, $campaign->id);
        $activityStats = $this->activityStatisticsService->getCampaignActivityStats($campaign->id);
        $upcomingActivities = $this->activityStatisticsService->getUpcomingActivities(5, $campaign->id);
        $recentActivities = $this->activityStatisticsService->getRecentActivities(10, $campaign->id);
        $dailySchedule = $this->campaignDailyBreakdownService->getDailyBreakdown($campaign);
        $surgeryDaysSchedule = $this->campaignDailyBreakdownService->getSurgeryDaysSchedule($campaign);

        $campaign->load([
            'patients.eligibilityStatus',
            'patients.currentStage',
        ]);

        return view('pages.campaigns.show', [
            'campaign' => $campaign,
            'statuses' => $this->lookupService->getCampaignStatuses(),
            'availableMembers' => Member::query()
                ->with(['memberRole', 'specialty'])
                ->where('status', 'active')
                ->when($assignedMemberIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $assignedMemberIds))
                ->orderBy('full_name')
                ->get(),
            'patientStats' => $patientStats,
            'stageStats'   => $stageStats,
            'attendanceStats' => $attendanceStats,
            'recentAttendances' => $recentAttendances,
            'transportStats' => $transportStats,
            'recentTrips' => $recentTrips,
            'activityStats' => $activityStats,
            'upcomingActivities' => $upcomingActivities,
            'recentActivities' => $recentActivities,
            'dailySchedule' => $dailySchedule,
            'surgeryDaysSchedule' => $surgeryDaysSchedule,
            'futureStats' => [
                'patients_count' => $patientStats['total'],
                'members_count' => $campaign->campaignMemberAssignments()->count(),
                'attendance_count' => $attendanceCount,
                'trips_count' => $transportStats['total'],
                'activities_count' => $activityStats['total'],
            ],
        ]);
    }

    public function edit(Campaign $campaign): View
    {
        $this->authorize('update', $campaign);

        $campaign->load(['country', 'city', 'specialty', 'campaignStatus']);

        return view('pages.campaigns.edit', [
            'campaign' => $campaign,
            ...$this->formData(),
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $this->campaignService->updateCampaign(
            $campaign,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('campaigns.show', $campaign)
            ->with('success', __('campaigns.messages.updated'));
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->authorize('delete', $campaign);

        $this->campaignService->deleteCampaign($campaign);

        return redirect()
            ->route('campaigns.index')
            ->with('success', __('campaigns.messages.deleted'));
    }

    public function changeStatus(ChangeCampaignStatusRequest $request, Campaign $campaign): RedirectResponse
    {
        $this->campaignService->changeStatus(
            $campaign,
            (int) $request->validated('campaign_status_id'),
            $request->user()
        );

        return back()->with('success', __('campaigns.messages.status_changed'));
    }

    public function assignMember(AssignMemberToCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $member = $request->resolveMember();

        try {
            $this->memberService->assignToCampaign(
                $member,
                $campaign,
                $request->validated(),
                $request->user()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('members.messages.assigned'))->withFragment('team');
    }

    public function removeMember(Campaign $campaign, Member $member): RedirectResponse
    {
        abort_unless($requestUser = request()->user(), 403);
        abort_unless($requestUser->can('member.assign_campaign'), 403);

        try {
            $this->memberService->removeFromCampaign($member, $campaign);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('members.messages.removed'))->withFragment('team');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'statuses' => $this->lookupService->getCampaignStatuses(),
        ];
    }
}
