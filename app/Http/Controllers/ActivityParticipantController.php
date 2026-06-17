<?php

namespace App\Http\Controllers;

use App\Http\Requests\Activity\AddParticipantRequest;
use App\Http\Requests\Activity\BulkAddParticipantsRequest;
use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class ActivityParticipantController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService,
    ) {}

    public function store(AddParticipantRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('manageParticipants', $activity);

        try {
            $this->activityService->addParticipant($activity, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['participant' => $e->getMessage()]);
        }

        return back()->with('success', __('activities.messages.participant_added'));
    }

    public function bulkStore(BulkAddParticipantsRequest $request, Activity $activity): RedirectResponse
    {
        $this->authorize('manageParticipants', $activity);

        $rows = $request->validated('rows') ?? [];

        foreach ($this->filterParticipantIds($request->input('patient_ids', [])) as $patientId) {
            $rows[] = [
                'participant_type' => \App\Enums\PassengerType::Patient->value,
                'patient_id' => $patientId,
            ];
        }

        foreach ($this->filterParticipantIds($request->input('member_ids', [])) as $memberId) {
            $rows[] = [
                'participant_type' => \App\Enums\PassengerType::Member->value,
                'member_id' => $memberId,
            ];
        }

        $result = $this->activityService->bulkAddParticipants(
            $activity,
            $rows,
            $request->user()
        );

        if ($result['added'] === 0) {
            return back()->with('error', __('activities.messages.bulk_none_added', $result));
        }

        return back()->with('success', __('activities.messages.bulk_added', $result));
    }

    /** @param  array<int, mixed>  $ids
     * @return list<int>
     */
    private function filterParticipantIds(array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function destroy(Activity $activity, ActivityParticipant $participant): RedirectResponse
    {
        $this->authorize('manageParticipants', $activity);

        abort_unless($participant->activity_id === $activity->id, 404);

        try {
            $this->activityService->removeParticipant($participant, request()->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['participant' => $e->getMessage()]);
        }

        return back()->with('success', __('activities.messages.participant_removed'));
    }
}
