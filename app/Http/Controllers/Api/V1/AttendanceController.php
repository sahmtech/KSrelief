<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\InteractsWithCampaignAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\BulkAttendanceRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Member;
use App\Services\AttendanceService;
use App\Services\AttendanceStatisticsService;
use App\Services\PatientAccessService;
use App\Support\ApiOperationsPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AttendanceController extends Controller
{
    use InteractsWithCampaignAccess;

    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceStatisticsService $statisticsService,
        private readonly PatientAccessService $campaignAccess,
    ) {}

    public function index(Request $request): JsonResponse
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

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = Attendance::query()
            ->with([
                'campaign',
                'member.memberRole',
                'member.specialty',
                'attendanceStatus',
                'recorder',
            ]);

        $this->campaignAccess->scopeVisibleByCampaign($query, $request->user());

        $attendances = $query
            ->search($filters['search'])
            ->filter($filters)
            ->orderByDesc('attendance_date')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => AttendanceResource::collection($attendances),
            'meta' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
            ],
            'filters' => array_filter($filters, fn ($value) => filled($value)),
            'stats' => $this->statisticsService->getTodayStats(
                $filters['campaign_id'] ? (int) $filters['campaign_id'] : null
            ),
            'permissions' => ApiOperationsPermissions::forAttendance($request->user()),
        ]);
    }

    public function quickSheet(Request $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);

        $campaignId = $request->integer('campaign_id') ?: null;
        $date = $request->query('attendance_date', now()->toDateString());
        $shift = (int) ($request->query('shift_number') ?: 1);

        if (! $campaignId) {
            return response()->json([
                'message' => __('attendance.errors.campaign_required_for_quick'),
            ], 422);
        }

        $this->assertCampaignAccessible($campaignId);

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
            ->get()
            ->map(fn (Member $member) => [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'role' => $member->memberRole?->name,
                'specialty' => $member->specialty?->name,
            ]);

        $existing = Attendance::query()
            ->with(['attendanceStatus'])
            ->where('campaign_id', $campaignId)
            ->whereDate('attendance_date', $date)
            ->where('shift_number', $shift)
            ->get()
            ->keyBy('member_id')
            ->map(fn (Attendance $row) => AttendanceResource::make($row));

        return response()->json([
            'campaign_id' => $campaignId,
            'attendance_date' => $date,
            'shift_number' => $shift,
            'members' => $members,
            'existing_by_member_id' => $existing,
            'stats' => $this->statisticsService->getCampaignStats($campaignId, $date),
            'permissions' => ApiOperationsPermissions::forAttendance($request->user()),
        ]);
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $this->assertCampaignAccessible((int) $request->validated('campaign_id'));

        try {
            $attendance = $this->attendanceService->createAttendance($request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $attendance->load(['campaign', 'member.memberRole', 'member.specialty', 'attendanceStatus', 'recorder']);

        return response()->json([
            'message' => __('attendance.messages.created'),
            'data' => AttendanceResource::make($attendance),
        ], 201);
    }

    public function bulkStore(BulkAttendanceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $this->assertCampaignAccessible((int) $validated['campaign_id']);

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
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => __('attendance.messages.bulk_saved', $result),
            'result' => $result,
        ]);
    }

    public function show(Request $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('view', $attendance);
        $this->assertModelCampaignAccessible($attendance);

        $attendance->load([
            'campaign.country',
            'member.memberRole',
            'member.specialty',
            'attendanceStatus',
            'recorder',
        ]);

        return response()->json([
            'data' => AttendanceResource::make($attendance),
            'permissions' => ApiOperationsPermissions::forAttendance($request->user(), $attendance),
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('update', $attendance);
        $this->assertModelCampaignAccessible($attendance);
        $this->assertCampaignAccessible((int) $request->validated('campaign_id'));

        try {
            $this->attendanceService->updateAttendance($attendance, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $attendance->refresh()->load(['campaign', 'member.memberRole', 'member.specialty', 'attendanceStatus', 'recorder']);

        return response()->json([
            'message' => __('attendance.messages.updated'),
            'data' => AttendanceResource::make($attendance),
        ]);
    }

    public function destroy(Request $request, Attendance $attendance): JsonResponse
    {
        $this->authorize('delete', $attendance);
        $this->assertModelCampaignAccessible($attendance);

        $this->attendanceService->deleteAttendance($attendance);

        return response()->json([
            'message' => __('attendance.messages.deleted'),
        ]);
    }
}
