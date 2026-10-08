<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\InteractsWithCampaignAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\AddParticipantRequest;
use App\Http\Requests\Activity\BulkAddParticipantsRequest;
use App\Http\Resources\ActivityParticipantResource;
use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class ActivityParticipantController extends Controller
{
    use InteractsWithCampaignAccess;

    public function __construct(
        private readonly ActivityService $activityService,
    ) {}

    public function store(AddParticipantRequest $request, Activity $activity): JsonResponse
    {
        $this->authorize('manageParticipants', $activity);
        $this->assertModelCampaignAccessible($activity);

        try {
            $participant = $this->activityService->addParticipant($activity, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $participant->load(['member', 'patient']);

        return response()->json([
            'message' => __('activities.messages.participant_added'),
            'data' => ActivityParticipantResource::make($participant),
        ], 201);
    }

    public function bulkStore(BulkAddParticipantsRequest $request, Activity $activity): JsonResponse
    {
        $this->authorize('manageParticipants', $activity);
        $this->assertModelCampaignAccessible($activity);

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
            return response()->json([
                'message' => __('activities.messages.bulk_none_added', $result),
                'result' => $result,
            ], 422);
        }

        return response()->json([
            'message' => __('activities.messages.bulk_added', $result),
            'result' => $result,
        ]);
    }

    public function destroy(Activity $activity, ActivityParticipant $participant): JsonResponse
    {
        $this->authorize('manageParticipants', $activity);
        $this->assertModelCampaignAccessible($activity);

        abort_unless($participant->activity_id === $activity->id, 404);

        try {
            $this->activityService->removeParticipant($participant, request()->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => __('activities.messages.participant_removed'),
        ]);
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
}
