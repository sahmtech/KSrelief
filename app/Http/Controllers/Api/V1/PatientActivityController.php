<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\InteractsWithPatientAccess;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Patient;
use App\Services\ActivityStatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientActivityController extends Controller
{
    use InteractsWithPatientAccess;

    public function __construct(private readonly ActivityStatisticsService $activityStatisticsService) {}

    public function index(Request $request, Patient $patient): JsonResponse
    {
        abort_unless($request->user()?->can('activity.view'), 403);
        $this->authorize('view', $patient);
        $this->assertPatientAccessible($patient);

        $limit = min(max((int) $request->query('limit', 20), 1), 100);

        return response()->json([
            'stats' => $this->activityStatisticsService->getParticipantStats(patientId: $patient->id),
            'data' => ActivityResource::collection(
                $this->activityStatisticsService->getPatientActivities($patient->id, $limit)
            ),
        ]);
    }
}
