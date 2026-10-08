<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\InteractsWithPatientAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\MedicalRecordResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\PatientStageHistoryResource;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\ActivityStatisticsService;
use App\Services\MedicalRecordService;
use App\Services\PatientAccessService;
use App\Services\PatientBriefService;
use App\Services\PatientClinicalProfileService;
use App\Services\PatientService;
use App\Services\PatientStatisticsService;
use App\Services\PatientWorkflowService;
use App\Services\TransportationStatisticsService;
use App\Support\ApiPatientPermissions;
use App\Support\PatientFileNumberStyleSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientController extends Controller
{
    use InteractsWithPatientAccess;

    public function __construct(
        private readonly PatientService $patientService,
        private readonly PatientAccessService $patientAccess,
        private readonly PatientStatisticsService $statisticsService,
        private readonly PatientWorkflowService $workflowService,
        private readonly MedicalRecordService $recordService,
        private readonly PatientBriefService $briefService,
        private readonly TransportationStatisticsService $transportationStatisticsService,
        private readonly ActivityStatisticsService $activityStatisticsService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Patient::class);

        $filters = [
            'search' => $request->query('search'),
            'campaign_id' => $request->query('campaign_id'),
            'eligibility_status_id' => $request->query('eligibility_status_id'),
            'current_stage_id' => $request->query('current_stage_id'),
            'admission_status' => $request->query('admission_status'),
            'gender' => $request->query('gender'),
            'created_from' => $request->query('created_from'),
            'created_to' => $request->query('created_to'),
        ];

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = Patient::query()
            ->with(['campaign', 'eligibilityStatus', 'currentStage']);

        $this->patientAccess->scopeVisiblePatients($query, $request->user());

        $patients = $query
            ->search($filters['search'])
            ->filter($filters)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        PatientFileNumberStyleSupport::applyAccentColors($patients->getCollection());

        return response()->json([
            'data' => PatientResource::collection($patients),
            'meta' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'per_page' => $patients->perPage(),
                'total' => $patients->total(),
            ],
            'filters' => array_filter($filters, fn ($value) => filled($value)),
            'stats' => $this->statisticsService->getPatientCounts(),
        ]);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = $this->patientService->createPatient(
            $request->safe()->except(['attachments', 'photo']),
            $request->user(),
            $request->file('attachments', []),
            $request->file('photo')
        );

        $this->recordService->createPreOperationRecordIfFilled(
            $patient,
            $request->all(),
            $request->user()
        );

        $patient->load(['campaign', 'eligibilityStatus', 'currentStage']);

        return response()->json([
            'message' => __('patients.messages.created'),
            'data' => PatientResource::make($patient),
        ], 201);
    }

    public function show(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('view', $patient);
        $this->assertPatientAccessible($patient);

        $user = $request->user();

        $relations = [
            'campaign.country',
            'campaign.city',
            'eligibilityStatus',
            'currentStage',
            'attachments.uploader',
            'creator',
            'updater',
        ];

        if ($user->can('viewStageHistory', $patient)) {
            $relations = array_merge($relations, [
                'stageHistories.fromStage',
                'stageHistories.toStage',
                'stageHistories.changedBy',
            ]);
        }

        if ($user->can('viewAny', [MedicalRecord::class, $patient])) {
            $relations = array_merge($relations, [
                'medicalRecords.stage',
                'medicalRecords.submitter',
                'medicalRecords.specialty',
            ]);
        }

        $patient->load($relations);

        PatientFileNumberStyleSupport::applyAccentColors(collect([$patient]));

        $payload = [
            'patient' => PatientResource::make($patient),
            'permissions' => ApiPatientPermissions::for($user, $patient),
        ];

        $payload['workflow'] = [];

        if ($user->can('viewWorkflow', $patient)) {
            $payload['workflow']['timeline'] = $this->formatTimeline($this->workflowService->getTimeline($patient));
            $payload['workflow']['available_stages'] = PatientStage::query()->active()->ordered()->get()->map(fn (PatientStage $stage) => [
                'id' => $stage->id,
                'name' => $stage->displayName(),
                'code' => $stage->code,
                'color' => $stage->color,
                'sort_order' => $stage->sort_order,
            ]);
        }

        if ($user->can('viewStageHistory', $patient)) {
            $payload['workflow']['history'] = PatientStageHistoryResource::collection(
                $patient->stageHistories->sortByDesc('changed_at')->values()
            );
        }

        if ($user->can('viewAny', [MedicalRecord::class, $patient])) {
            $records = $patient->medicalRecords->sortByDesc('record_date')->values();
            $payload['medical_records'] = MedicalRecordResource::collection($records);
            $payload['clinical_profile'] = app(PatientClinicalProfileService::class)->buildProfile($patient);
            $payload['screening_field_definitions'] = $this->recordService->getScreeningFields();
            $payload['clinical_phases'] = $this->recordService->clinicalPhases();
        }

        if ($user->can('transportation.view')) {
            $payload['transportation'] = [
                'stats' => $this->transportationStatisticsService->getPatientTransportStats($patient->id),
                'trips' => $this->transportationStatisticsService->getPatientTrips($patient->id),
            ];
        }

        if ($user->can('activity.view')) {
            $payload['activities'] = [
                'stats' => $this->activityStatisticsService->getParticipantStats(patientId: $patient->id),
                'items' => ActivityResource::collection(
                    $this->activityStatisticsService->getPatientActivities($patient->id, (int) $request->query('activities_limit', 20))
                ),
            ];
        }

        return response()->json($payload);
    }

    public function brief(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('view', $patient);
        $this->assertPatientAccessible($patient);

        $user = $request->user();

        $patient->load([
            'campaign.country',
            'campaign.city',
            'eligibilityStatus',
            'currentStage',
            'attachments' => fn ($query) => $query->with('uploader')->latest(),
        ]);

        PatientFileNumberStyleSupport::applyAccentColors(collect([$patient]));

        $clinicalProfile = $user->can('viewAny', [MedicalRecord::class, $patient])
            ? app(PatientClinicalProfileService::class)->buildProfile($patient)
            : null;

        return response()->json([
            'patient' => PatientResource::make($patient),
            'brief' => $this->briefService->build($patient, $clinicalProfile),
            'permissions' => ApiPatientPermissions::for($user, $patient),
        ]);
    }

    public function update(UpdatePatientRequest $request, Patient $patient): JsonResponse
    {
        $this->assertPatientAccessible($patient);

        $patient = $this->patientService->updatePatient(
            $patient,
            $request->safe()->except(['attachments', 'photo', 'remove_photo']),
            $request->user(),
            $request->file('attachments', []),
            $request->file('photo'),
            $request->boolean('remove_photo')
        );

        $patient->load(['campaign', 'eligibilityStatus', 'currentStage']);

        return response()->json([
            'message' => __('patients.messages.updated'),
            'data' => PatientResource::make($patient),
        ]);
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $this->authorize('delete', $patient);
        $this->assertPatientAccessible($patient);

        $this->patientService->deletePatient($patient);

        return response()->json([
            'message' => __('patients.messages.deleted'),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $timeline
     * @return list<array<string, mixed>>
     */
    private function formatTimeline(array $timeline): array
    {
        return collect($timeline)->map(function (array $item): array {
            /** @var PatientStage $stage */
            $stage = $item['stage'];
            $history = $item['history'] ?? null;

            return [
                'stage' => [
                    'id' => $stage->id,
                    'name' => $stage->displayName(),
                    'code' => $stage->code,
                    'color' => $stage->color,
                    'sort_order' => $stage->sort_order,
                ],
                'completed' => (bool) ($item['completed'] ?? false),
                'current' => (bool) ($item['current'] ?? false),
                'pending' => (bool) ($item['pending'] ?? false),
                'history' => $history ? [
                    'id' => $history->id,
                    'changed_at' => $history->changed_at?->toIso8601String(),
                    'notes' => $history->notes,
                    'changed_by' => $history->relationLoaded('changedBy') && $history->changedBy ? [
                        'id' => $history->changedBy->id,
                        'name' => $history->changedBy->name,
                    ] : null,
                ] : null,
            ];
        })->values()->all();
    }
}
