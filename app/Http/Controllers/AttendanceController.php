<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\BulkAttendanceRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\Member;
use App\Services\AttendanceService;
use App\Services\AttendanceStatisticsService;
use App\Services\LookupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceStatisticsService $statisticsService,
        private readonly LookupService $lookupService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $filters = [
            'search' => $request->query('search'),
            'campaign_id' => $request->query('campaign_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'shift_number' => $request->query('shift_number'),
            'attendance_status_id' => $request->query('attendance_status_id'),
            'member_role_id' => $request->query('member_role_id'),
            'specialty_id' => $request->query('specialty_id'),
        ];

        $attendances = Attendance::query()
            ->with([
                'campaign',
                'member.memberRole',
                'member.specialty',
                'attendanceStatus',
                'recorder',
            ])
            ->search($filters['search'])
            ->filter($filters)
            ->orderByDesc('attendance_date')
            ->orderByDesc('created_at')
            ->get();

        return view('pages.attendance.index', [
            'attendances' => $attendances,
            'stats' => $this->statisticsService->getTodayStats(
                $filters['campaign_id'] ? (int) $filters['campaign_id'] : null
            ),
            'filters' => $filters,
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'attendanceStatuses' => $this->lookupService->getAttendanceStatuses(),
            'memberRoles' => $this->lookupService->getMemberRoles(),
            'specialties' => $this->lookupService->getSpecialties(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Attendance::class);

        return view('pages.attendance.create', $this->formData($request));
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        try {
            $this->attendanceService->createAttendance($request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['member_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('operations.attendance.index')
            ->with('success', __('attendance.messages.created'));
    }

    public function show(Attendance $attendance): View
    {
        $this->authorize('view', $attendance);

        $attendance->load([
            'campaign.country',
            'member.memberRole',
            'member.specialty',
            'attendanceStatus',
            'recorder',
        ]);

        return view('pages.attendance.show', [
            'attendance' => $attendance,
        ]);
    }

    public function edit(Attendance $attendance): View
    {
        $this->authorize('update', $attendance);

        $attendance->load(['campaign', 'member']);

        return view('pages.attendance.edit', array_merge(
            $this->formData(request()),
            ['attendance' => $attendance]
        ));
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        try {
            $this->attendanceService->updateAttendance($attendance, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['member_id' => $e->getMessage()]);
        }

        return redirect()
            ->route('operations.attendance.index')
            ->with('success', __('attendance.messages.updated'));
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $this->authorize('delete', $attendance);

        $this->attendanceService->deleteAttendance($attendance);

        return redirect()
            ->route('operations.attendance.index')
            ->with('success', __('attendance.messages.deleted'));
    }

    public function quickAttendance(Request $request): View
    {
        $this->authorize('create', Attendance::class);

        $campaignId = $request->integer('campaign_id') ?: null;
        $date = $request->query('attendance_date', now()->toDateString());
        $shift = (int) ($request->query('shift_number') ?: 1);

        $members = collect();
        $existing = collect();

        if ($campaignId) {
            $members = Member::query()
                ->with(['memberRole', 'specialty'])
                ->whereHas('campaignAssignments', function ($q) use ($campaignId): void {
                    $q->where('campaign_id', $campaignId)
                        ->where(function ($query): void {
                            $query->whereNull('assigned_to')
                                ->orWhereDate('assigned_to', '>=', now());
                        });
                })
                ->orderBy('full_name')
                ->get();

            $existing = Attendance::query()
                ->where('campaign_id', $campaignId)
                ->whereDate('attendance_date', $date)
                ->where('shift_number', $shift)
                ->get()
                ->keyBy('member_id');
        }

        return view('pages.attendance.quick', [
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'attendanceStatuses' => $this->lookupService->getAttendanceStatuses(),
            'campaignId' => $campaignId,
            'attendanceDate' => $date,
            'shiftNumber' => $shift,
            'members' => $members,
            'existing' => $existing,
            'stats' => $campaignId
                ? $this->statisticsService->getCampaignStats($campaignId, $date)
                : null,
        ]);
    }

    public function bulkStore(BulkAttendanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $rows = [];

        foreach ($validated['rows'] as $row) {
            $rows[] = [
                'campaign_id' => $validated['campaign_id'],
                'attendance_date' => $validated['attendance_date'],
                'shift_number' => $validated['shift_number'] ?? 1,
                'member_id' => $row['member_id'],
                'attendance_status_id' => $row['attendance_status_id'],
                'check_in' => $row['check_in'] ?? null,
                'check_out' => $row['check_out'] ?? null,
                'notes' => $row['notes'] ?? null,
            ];
        }

        try {
            $result = $this->attendanceService->bulkStore($rows, $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['bulk' => $e->getMessage()]);
        }

        return redirect()
            ->route('operations.attendance.quick', [
                'campaign_id' => $validated['campaign_id'],
                'attendance_date' => $validated['attendance_date'],
                'shift_number' => $validated['shift_number'] ?? 1,
            ])
            ->with('success', __('attendance.messages.bulk_saved', $result));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        $campaignId = $request->integer('campaign_id') ?: null;

        $members = collect();

        if ($campaignId) {
            $members = Member::query()
                ->with(['memberRole', 'specialty'])
                ->whereHas('campaignAssignments', fn ($q) => $q->where('campaign_id', $campaignId))
                ->orderBy('full_name')
                ->get();
        }

        return [
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'attendanceStatuses' => $this->lookupService->getAttendanceStatuses(),
            'members' => $members,
            'selectedCampaignId' => $campaignId,
        ];
    }
}
