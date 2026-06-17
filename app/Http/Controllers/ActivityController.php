<?php

namespace App\Http\Controllers;

use App\Enums\ActivityStatus;
use App\Http\Requests\Activity\ChangeActivityStatusRequest;
use App\Http\Requests\Activity\RescheduleActivityRequest;
use App\Http\Requests\Activity\StoreActivityRequest;
use App\Http\Requests\Activity\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\Campaign;
use App\Models\Member;
use App\Models\Patient;
use App\Services\ActivityService;
use App\Services\ActivityStatisticsService;
use App\Services\LookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService,
        private readonly ActivityStatisticsService $statisticsService,
        private readonly LookupService $lookupService,
    ) {}

    public function index(Request $request): View
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

        $activities = Activity::query()
            ->with(['campaign', 'activityType', 'creator'])
            ->withCount('participants')
            ->search($filters['search'])
            ->filter($filters)
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->get();

        return view('pages.activities.index', [
            'activities' => $activities,
            'stats' => $this->statisticsService->getActivityStats(
                $filters['campaign_id'] ? (int) $filters['campaign_id'] : null
            ),
            'filters' => $filters,
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'activityTypes' => $this->lookupService->getActivityTypes(limit: 200),
            'activityStatuses' => ActivityStatus::cases(),
        ]);
    }

    public function calendar(Request $request): View
    {
        $this->authorize('viewAny', Activity::class);

        return view('pages.activities.calendar', [
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'activityTypes' => $this->lookupService->getActivityTypes(limit: 200),
            'filters' => [
                'campaign_id' => $request->query('campaign_id'),
                'activity_type_id' => $request->query('activity_type_id'),
            ],
            'canCreate' => $request->user()->can('activity.create'),
            'canUpdate' => $request->user()->can('activity.update'),
        ]);
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Activity::class);

        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end = $request->query('end', now()->endOfMonth()->toDateString());

        $activities = $this->statisticsService->getCalendarActivities(
            $start,
            $end,
            $request->integer('campaign_id') ?: null,
            $request->integer('activity_type_id') ?: null,
        );

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
                'url' => route('operations.activities.show', $activity),
                'editable' => $activity->isEditable() && $request->user()->can('activity.update'),
            ],
        ]);

        return response()->json($events);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Activity::class);

        return view('pages.activities.create', $this->formData($request));
    }

    public function store(StoreActivityRequest $request): RedirectResponse
    {
        $activity = $this->activityService->createActivity($request->validated(), $request->user());

        return redirect()
            ->route('operations.activities.show', $activity)
            ->with('success', __('activities.messages.created'));
    }

    public function show(Activity $activity): View
    {
        $this->authorize('view', $activity);

        $activity->load([
            'campaign.country',
            'activityType',
            'patientStage',
            'creator',
            'updater',
            'participants' => fn ($query) => $query->orderBy('id'),
            'participants.member.memberRole',
            'participants.patient',
            'statusLogs.changedBy',
        ]);

        $campaignMembers = Member::query()
            ->with(['memberRole'])
            ->whereHas('campaignAssignments', fn ($q) => $q->where('campaign_id', $activity->campaign_id))
            ->orderBy('full_name')
            ->get();

        $campaignPatients = Patient::query()
            ->where('campaign_id', $activity->campaign_id)
            ->orderBy('patient_name')
            ->get(['id', 'patient_name', 'file_number']);

        $existingMemberIds = $activity->participants->pluck('member_id')->filter()->all();
        $existingPatientIds = $activity->participants->pluck('patient_id')->filter()->all();

        return view('pages.activities.show', [
            'activity' => $activity,
            'campaignMembers' => $campaignMembers->whereNotIn('id', $existingMemberIds),
            'campaignPatients' => $campaignPatients->whereNotIn('id', $existingPatientIds),
            'statusTransitions' => $activity->status->allowedTransitions(),
            'participantStats' => [
                'total' => $activity->participants->count(),
                'members' => $activity->participants->where('participant_type', \App\Enums\PassengerType::Member)->count(),
                'patients' => $activity->participants->where('participant_type', \App\Enums\PassengerType::Patient)->count(),
            ],
        ]);
    }

    public function edit(Activity $activity): View
    {
        $this->authorize('update', $activity);

        $activity->load(['campaign', 'activityType', 'patientStage']);

        return view('pages.activities.edit', array_merge(
            $this->formData(request()),
            ['activity' => $activity]
        ));
    }

    public function update(UpdateActivityRequest $request, Activity $activity): RedirectResponse
    {
        try {
            $this->activityService->updateActivity($activity, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['activity' => $e->getMessage()]);
        }

        return redirect()
            ->route('operations.activities.show', $activity)
            ->with('success', __('activities.messages.updated'));
    }

    public function reschedule(RescheduleActivityRequest $request, Activity $activity): JsonResponse
    {
        $this->authorize('update', $activity);

        try {
            $this->activityService->rescheduleActivity($activity, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        try {
            $this->activityService->deleteActivity($activity);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['activity' => $e->getMessage()]);
        }

        return redirect()
            ->route('operations.activities.index')
            ->with('success', __('activities.messages.deleted'));
    }

    public function changeStatus(ChangeActivityStatusRequest $request, Activity $activity): RedirectResponse
    {
        try {
            $this->activityService->changeStatus(
                $activity,
                ActivityStatus::from($request->validated('status')),
                $request->user(),
                $request->validated('notes')
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', __('activities.messages.status_changed'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        return [
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'activityTypes' => $this->lookupService->getActivityTypes(limit: 200),
            'patientStages' => $this->lookupService->getPatientStages(limit: 200),
            'selectedCampaignId' => $request->integer('campaign_id') ?: null,
            'prefillDate' => $request->query('activity_date'),
            'prefillStart' => $request->query('start_time'),
            'prefillEnd' => $request->query('end_time'),
        ];
    }
}
