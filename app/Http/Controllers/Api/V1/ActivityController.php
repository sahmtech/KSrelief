<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ActivityStatus;
use App\Http\Controllers\Api\V1\Concerns\InteractsWithCampaignAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\ChangeActivityStatusRequest;
use App\Http\Requests\Activity\RescheduleActivityRequest;
use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Member;
use App\Models\Patient;
use App\Services\ActivityService;
use App\Services\ActivityStatisticsService;
use App\Services\PatientAccessService;
use App\Support\ApiOperationsPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ActivityController extends Controller
{
    use InteractsWithCampaignAccess;

    public function __construct(
        private readonly ActivityService $activityService,
        private readonly ActivityStatisticsService $statisticsService,
        private readonly PatientAccessService $campaignAccess,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $filters = [
            'search' => $request->query('search'),
            'campaign_id' => $request->query('campaign_id'),
            'activity_type_id' => $request->query('activity_type_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'status' => $request->query('status'),
        ];

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = Activity::query()
            ->with(['campaign', 'activityType', 'creator'])
            ->withCount('participants');

        $this->campaignAccess->scopeVisibleByCampaign($query, $request->user());

        $activities = $query
            ->search($filters['search'])
            ->filter($filters)
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->paginate($perPage);

        return response()->json([
            'data' => ActivityResource::collection($activities),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
            'filters' => array_filter($filters, fn ($value) => filled($value)),
            'stats' => $this->statisticsService->getActivityStats(
                $filters['campaign_id'] ? (int) $filters['campaign_id'] : null
            ),
            'permissions' => ApiOperationsPermissions::forActivity($request->user()),
        ]);
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end = $request->query('end', now()->endOfMonth()->toDateString());
        $campaignId = $request->integer('campaign_id') ?: null;
        $activityTypeId = $request->integer('activity_type_id') ?: null;

        if ($campaignId) {
            $this->assertCampaignAccessible($campaignId);
        }

        $activities = $this->statisticsService->getCalendarActivities(
            $start,
            $end,
            $campaignId,
            $activityTypeId,
        );

        $user = $request->user();
        $allowed = $this->campaignAccess->allowedCampaignIds($user);

        if ($allowed !== null) {
            $activities = $activities->filter(
                fn (Activity $activity) => in_array((int) $activity->campaign_id, $allowed, true)
            );
        }

        $events = $activities->map(fn (Activity $activity) => [
            'id' => $activity->id,
            'title' => $activity->title,
            'start' => $activity->calendarStart(),
            'end' => $activity->calendarEnd(),
            'backgroundColor' => $activity->calendarColor(),
            'borderColor' => $activity->calendarColor(),
            'extendedProps' => [
                'campaign' => $activity->campaign?->name,
                'type' => $activity->activityType?->name,
                'status' => $activity->statusLabel(),
                'location' => $activity->location,
                'participants' => $activity->participants_count,
                'editable' => $activity->isEditable() && $user->can('activity.update'),
            ],
        ])->values();

        return response()->json(['data' => $events]);
    }

    public function store(StoreActivityRequest $request): JsonResponse
    {
        $this->assertCampaignAccessible((int) $request->validated('campaign_id'));

        try {
            $activity = $this->activityService->createActivity($request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $activity->load(['campaign', 'activityType', 'creator']);

        return response()->json([
            'message' => __('activities.messages.created'),
            'data' => ActivityResource::make($activity),
        ], 201);
    }

    public function show(Request $request, Activity $activity): JsonResponse
    {
        $this->authorize('view', $activity);
        $this->assertModelCampaignAccessible($activity);

        $activity->load([
            'campaign.country',
            'activityType',
            'patientStage',
            'creator',
            'updater',
            'participants' => fn ($query) => $query->orderBy('id'),
            'participants.member.memberRole',
            'participants.patient',
        ]);

        return response()->json([
            'data' => ActivityResource::make($activity),
            'status_transitions' => collect($activity->status->allowedTransitions())->map(fn (ActivityStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ])->values(),
            'participant_stats' => [
                'total' => $activity->participants->count(),
                'members' => $activity->participants->where('participant_type', \App\Enums\PassengerType::Member)->count(),
                'patients' => $activity->participants->where('participant_type', \App\Enums\PassengerType::Patient)->count(),
            ],
            'permissions' => ApiOperationsPermissions::forActivity($request->user(), $activity),
        ]);
    }

    public function participantOptions(Request $request, Activity $activity): JsonResponse
    {
        $this->authorize('manageParticipants', $activity);
        $this->assertModelCampaignAccessible($activity);

        $campaignMembers = Member::query()
            ->with(['memberRole'])
            ->whereHas('campaignAssignments', fn ($q) => $q->where('campaign_id', $activity->campaign_id))
            ->orderBy('full_name')
            ->get();

        $campaignPatients = Patient::query()
            ->where('campaign_id', $activity->campaign_id)
            ->orderBy('patient_name')
            ->get(['id', 'patient_name', 'file_number']);

        $existingMemberIds = $activity->participants()->pluck('member_id')->filter()->all();
        $existingPatientIds = $activity->participants()->pluck('patient_id')->filter()->all();

        return response()->json([
            'members' => $campaignMembers->whereNotIn('id', $existingMemberIds)->values()->map(fn (Member $m) => [
                'id' => $m->id,
                'full_name' => $m->full_name,
                'role' => $m->memberRole?->name,
            ]),
            'patients' => $campaignPatients->whereNotIn('id', $existingPatientIds)->values(),
        ]);
    }

    public function update(UpdateActivityRequest $request, Activity $activity): JsonResponse
    {
        $this->authorize('update', $activity);
        $this->assertModelCampaignAccessible($activity);

        if ($request->filled('campaign_id')) {
            $this->assertCampaignAccessible((int) $request->validated('campaign_id'));
        }

        try {
            $this->activityService->updateActivity($activity, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $activity->refresh()->load(['campaign', 'activityType', 'creator']);

        return response()->json([
            'message' => __('activities.messages.updated'),
            'data' => ActivityResource::make($activity),
        ]);
    }

    public function reschedule(RescheduleActivityRequest $request, Activity $activity): JsonResponse
    {
        $this->authorize('update', $activity);
        $this->assertModelCampaignAccessible($activity);

        try {
            $this->activityService->rescheduleActivity($activity, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $activity->refresh()->load(['campaign', 'activityType', 'creator']);

        return response()->json([
            'message' => __('activities.messages.updated'),
            'data' => ActivityResource::make($activity),
        ]);
    }

    public function changeStatus(ChangeActivityStatusRequest $request, Activity $activity): JsonResponse
    {
        $this->authorize('changeStatus', $activity);
        $this->assertModelCampaignAccessible($activity);

        try {
            $this->activityService->changeStatus(
                $activity,
                ActivityStatus::from($request->validated('status')),
                $request->user(),
                $request->validated('notes')
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $activity->refresh()->load(['campaign', 'activityType', 'creator']);

        return response()->json([
            'message' => __('activities.messages.status_changed'),
            'data' => ActivityResource::make($activity),
        ]);
    }

    public function destroy(Request $request, Activity $activity): JsonResponse
    {
        $this->authorize('delete', $activity);
        $this->assertModelCampaignAccessible($activity);

        try {
            $this->activityService->deleteActivity($activity);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => __('activities.messages.deleted'),
        ]);
    }
}
