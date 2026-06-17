<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Exports\MemberTemplateExport;
use App\Http\Requests\Member\AssignMemberToCampaignRequest;
use App\Http\Requests\Member\ImportMembersRequest;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Models\Campaign;
use App\Models\Member;
use App\Models\User;
use App\Services\AttendanceStatisticsService;
use App\Services\LookupService;
use App\Services\MemberImportService;
use App\Services\MemberService;
use App\Services\ActivityStatisticsService;
use App\Services\TransportationStatisticsService;
use App\Support\MemberImportResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService,
        private readonly MemberImportService $memberImportService,
        private readonly LookupService $lookupService,
        private readonly AttendanceStatisticsService $attendanceStatisticsService,
        private readonly TransportationStatisticsService $transportationStatisticsService,
        private readonly ActivityStatisticsService $activityStatisticsService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Member::class);

        $filters = [
            'search' => $request->query('search'),
            'member_role_id' => $request->query('member_role_id'),
            'specialty_id' => $request->query('specialty_id'),
            'status' => $request->query('status'),
        ];

        $members = Member::query()
            ->with(['memberRole', 'specialty'])
            ->withCount('campaignAssignments')
            ->search($filters['search'])
            ->filter($filters)
            ->orderBy('full_name')
            ->get();

        return view('pages.medical-staff.members.index', [
            'members' => $members,
            'stats' => $this->memberService->getDashboardStats(),
            'filters' => $filters,
            'memberRoles' => $this->lookupService->getMemberRoles(),
            'specialties' => $this->lookupService->getSpecialties(),
            'statuses' => MemberStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Member::class);

        return view('pages.medical-staff.members.create', $this->formData());
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $member = $this->memberService->createMember(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('medical-staff.members.show', $member)
            ->with('success', __('members.messages.created'));
    }

    public function show(Member $member): View
    {
        $this->authorize('view', $member);

        $member->load([
            'memberRole',
            'specialty',
            'user.roles',
            'creator',
            'updater',
            'campaignAssignments.campaign.country',
            'campaignAssignments.campaign.city',
            'campaignAssignments.creator',
        ]);

        $attendanceStats = $this->attendanceStatisticsService->getMemberStats($member->id);
        $recentAttendances = $this->attendanceStatisticsService->getRecentAttendances(10, null, $member->id);
        $transportStats = $this->transportationStatisticsService->getMemberTransportStats($member->id);
        $memberTrips = $this->transportationStatisticsService->getMemberTrips($member->id);
        $activityStats = $this->activityStatisticsService->getParticipantStats(memberId: $member->id);
        $memberActivities = $this->activityStatisticsService->getMemberActivities($member->id);

        return view('pages.medical-staff.members.show', [
            'member' => $member,
            'attendanceStats' => $attendanceStats,
            'recentAttendances' => $recentAttendances,
            'transportStats' => $transportStats,
            'memberTrips' => $memberTrips,
            'activityStats' => $activityStats,
            'memberActivities' => $memberActivities,
            'stats' => [
                'campaigns_count' => $member->campaignAssignments->count(),
                'attendance_count' => $attendanceStats['total'],
                'trips_count' => $transportStats['total'],
                'activities_count' => $activityStats['total'],
            ],
        ]);
    }

    public function edit(Member $member): View
    {
        $this->authorize('update', $member);

        $member->load(['memberRole', 'specialty', 'user']);

        return view('pages.medical-staff.members.edit', [
            'member' => $member,
            ...$this->formData($member),
        ]);
    }

    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $this->memberService->updateMember(
            $member,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('medical-staff.members.show', $member)
            ->with('success', __('members.messages.updated'));
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        $this->memberService->deleteMember($member);

        return redirect()
            ->route('medical-staff.members.index')
            ->with('success', __('members.messages.deleted'));
    }

    public function campaigns(Member $member): View
    {
        $this->authorize('assignCampaign', $member);

        $member->load([
            'memberRole',
            'campaignAssignments.campaign.country',
            'campaignAssignments.campaign.city',
            'campaignAssignments.creator',
        ]);

        $assignedCampaignIds = $member->campaignAssignments->pluck('campaign_id');

        return view('pages.medical-staff.members.campaigns', [
            'member' => $member,
            'campaigns' => Campaign::query()
                ->with(['country', 'city', 'campaignStatus'])
                ->when($assignedCampaignIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $assignedCampaignIds))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function assignCampaign(AssignMemberToCampaignRequest $request, Member $member): RedirectResponse
    {
        try {
            $this->memberService->assignToCampaign(
                $member,
                $request->resolveCampaign(),
                $request->validated(),
                $request->user()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('members.messages.assigned'));
    }

    public function removeCampaign(Member $member, Campaign $campaign): RedirectResponse
    {
        $this->authorize('assignCampaign', $member);

        try {
            $this->memberService->removeFromCampaign($member, $campaign);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('members.messages.removed'));
    }

    public function importForm(Request $request): View
    {
        $this->authorize('import', Member::class);

        $importResult = null;

        if ($request->session()->has('import_result')) {
            $importResult = MemberImportResult::fromArray($request->session()->get('import_result'));
            $request->session()->forget('import_result');
        }

        return view('pages.medical-staff.members.import', [
            'memberRoles' => $this->lookupService->getMemberRoles(),
            'specialties' => $this->lookupService->getSpecialties(),
            'importResult' => $importResult,
        ]);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $this->authorize('import', Member::class);

        return Excel::download(
            new MemberTemplateExport,
            'members_import_template.xlsx'
        );
    }

    public function import(ImportMembersRequest $request): RedirectResponse
    {
        $result = $this->memberImportService->import(
            $request->file('file'),
            $request->user()
        );

        if ($result->success > 0 && ! $result->hasErrors()) {
            return redirect()
                ->route('medical-staff.members.index')
                ->with('success', __('members.import.messages.completed', ['count' => $result->success]));
        }

        return redirect()
            ->route('medical-staff.members.import')
            ->with('import_result', $result->toArray())
            ->with($result->success > 0 ? 'success' : 'error', $result->success > 0
                ? __('members.import.messages.partial', ['success' => $result->success, 'failed' => $result->failed + $result->skipped])
                : __('members.import.messages.failed'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?Member $member = null): array
    {
        $linkedUserIds = Member::query()
            ->when($member, fn ($q) => $q->where('id', '!=', $member->id))
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $usersQuery = User::query()->orderBy('name');

        if ($member?->user_id) {
            $usersQuery->where(function ($q) use ($linkedUserIds, $member): void {
                $q->whereNotIn('id', $linkedUserIds)->orWhere('id', $member->user_id);
            });
        } else {
            $usersQuery->whereNotIn('id', $linkedUserIds);
        }

        return [
            'memberRoles' => $this->lookupService->getMemberRoles(),
            'specialties' => $this->lookupService->getSpecialties(),
            'statuses' => MemberStatus::cases(),
            'users' => $usersQuery->get(['id', 'name', 'email']),
        ];
    }
}
