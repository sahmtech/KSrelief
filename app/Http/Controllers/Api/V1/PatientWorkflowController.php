<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\InteractsWithPatientAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workflow\ChangePatientStageRequest;
use App\Http\Resources\PatientStageHistoryResource;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\PatientWorkflowService;
use Illuminate\Http\JsonResponse;

class PatientWorkflowController extends Controller
{
    use InteractsWithPatientAccess;

    public function __construct(private readonly PatientWorkflowService $workflowService) {}

    public function timeline(Patient $patient): JsonResponse
    {
        $this->authorize('viewWorkflow', $patient);
        $this->assertPatientAccessible($patient);

        $timeline = collect($this->workflowService->getTimeline($patient))->map(function (array $item): array {
            /** @var PatientStage $stage */
            $stage = $item['stage'];
            $history = $item['history'] ?? null;

            return [
                'stage' => [
                    'id' => $stage->id,
                    'name' => $stage->displayName(),
                    'code' => $stage->code,
                    'color' => $stage->color,
                ],
                'completed' => (bool) ($item['completed'] ?? false),
                'current' => (bool) ($item['current'] ?? false),
                'pending' => (bool) ($item['pending'] ?? false),
                'history' => $history ? [
                    'id' => $history->id,
                    'changed_at' => $history->changed_at?->toIso8601String(),
                    'notes' => $history->notes,
                ] : null,
            ];
        });

        return response()->json(['timeline' => $timeline]);
    }

    public function history(Patient $patient): JsonResponse
    {
        $this->authorize('viewStageHistory', $patient);
        $this->assertPatientAccessible($patient);

        $history = $this->workflowService->getHistory($patient);

        return response()->json([
            'history' => PatientStageHistoryResource::collection($history),
        ]);
    }

    public function changeStage(ChangePatientStageRequest $request, Patient $patient): JsonResponse
    {
        $this->authorize('changeStage', $patient);
        $this->assertPatientAccessible($patient);

        try {
            $this->workflowService->changeStage(
                patient: $patient,
                toStageId: (int) $request->validated('to_stage_id'),
                user: $request->user(),
                notes: $request->validated('notes'),
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $toStage = PatientStage::find($request->validated('to_stage_id'));

        return response()->json([
            'message' => __('workflow.messages.stage_changed'),
            'stage' => $toStage ? [
                'id' => $toStage->id,
                'name' => $toStage->displayName(),
                'code' => $toStage->code,
            ] : null,
        ]);
    }
}
