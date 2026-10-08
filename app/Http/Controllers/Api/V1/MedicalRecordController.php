<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\InteractsWithPatientAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workflow\StoreMedicalRecordRequest;
use App\Http\Requests\Workflow\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\CampaignOperationDefaultService;
use App\Services\FollowUpFormDefaultService;
use App\Services\LookupService;
use App\Services\MedicalRecordService;
use App\Services\OperationFormDefaultService;
use App\Services\OperationQuickFillService;
use App\Services\OperationReportPdfService;
use App\Services\PatientService;
use App\Services\PostOperationFormDefaultService;
use App\Services\PreOperationFormDefaultService;
use App\Support\OperationFieldSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MedicalRecordController extends Controller
{
    use InteractsWithPatientAccess;

    public function __construct(
        private readonly MedicalRecordService $recordService,
        private readonly LookupService $lookupService,
        private readonly PatientService $patientService,
        private readonly FollowUpFormDefaultService $followUpDefaultService,
        private readonly OperationFormDefaultService $operationDefaultService,
        private readonly PreOperationFormDefaultService $preOperationDefaultService,
        private readonly PostOperationFormDefaultService $postOperationDefaultService,
        private readonly CampaignOperationDefaultService $campaignOperationDefaultService,
        private readonly OperationQuickFillService $operationQuickFillService,
        private readonly OperationReportPdfService $operationReportPdfService,
    ) {}

    public function index(Patient $patient): JsonResponse
    {
        $this->authorize('viewAny', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        return response()->json([
            'data' => MedicalRecordResource::collection($this->recordService->getPatientRecords($patient)),
        ]);
    }

    public function schema(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('viewAny', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        $allStages = PatientStage::query()->active()->ordered()->get();
        $hiddenStageCodes = config('patient_clinical.record_form_hidden_stage_codes', []);

        $stages = $allStages->reject(
            fn (PatientStage $stage): bool => in_array($stage->code, $hiddenStageCodes, true)
        )->values();

        $stageId = $request->integer('stage_id') ?: null;
        $selectedStage = $stageId
            ? $allStages->firstWhere('id', $stageId)
            : $patient->currentStage;

        if ($selectedStage && in_array($selectedStage->code, $hiddenStageCodes, true)) {
            $selectedStage = $stages->first();
        }

        $stageCode = $selectedStage?->code ?? ($stages->first()?->code ?? 'pre_operation');
        $companyId = $request->integer('implant_company_id') ?: null;

        return response()->json([
            'stage_id' => $selectedStage?->id,
            'stage_code' => $stageCode,
            'stages' => $stages->map(fn (PatientStage $stage) => [
                'id' => $stage->id,
                'name' => $stage->displayName(),
                'code' => $stage->code,
            ]),
            'fields' => $this->recordService->getStageFields($stageCode),
            'team_members' => $this->formatTeamMembers($this->lookupService->getCampaignTeamMembers($patient->campaign_id)),
            'implant_companies' => $this->lookupService->getImplantCompanies()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'code' => $c->code,
            ])->values(),
            'insertion_approaches' => $this->lookupService->getInsertionApproaches()->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
            ])->values(),
            'electrode_types' => $companyId
                ? $this->lookupService->getImplantElectrodeTypes($companyId)->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                ])->values()
                : [],
            'template_flags' => [
                'has_follow_up_defaults' => $this->followUpDefaultService->hasForUser($request->user()),
                'has_operation_defaults' => $this->operationDefaultService->hasForUser($request->user()),
                'has_pre_operation_defaults' => $this->preOperationDefaultService->hasForUser($request->user()),
                'has_post_operation_defaults' => $this->postOperationDefaultService->hasForUser($request->user()),
                'has_campaign_operation_defaults' => $this->campaignOperationDefaultService->hasForCampaign($patient->campaign),
            ],
        ]);
    }

    public function store(StoreMedicalRecordRequest $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        $validated = $request->validated();
        $record = $this->recordService->createRecord($patient, $validated, $request->user());

        if ($request->hasFile('admission_attachments')) {
            foreach ($request->file('admission_attachments') as $file) {
                $this->patientService->uploadAttachment($patient, $file, $request->user());
            }
        }

        return response()->json([
            'message' => __('workflow.messages.record_created'),
            'data' => MedicalRecordResource::make($record),
        ], 201);
    }

    public function show(Patient $patient, MedicalRecord $record): JsonResponse
    {
        $this->authorize('view', $record);
        $this->assertPatientAccessible($patient);

        $record->load(['stage', 'submitter', 'specialty']);

        return response()->json([
            'data' => MedicalRecordResource::make($record),
        ]);
    }

    public function update(UpdateMedicalRecordRequest $request, Patient $patient, MedicalRecord $record): JsonResponse
    {
        $this->authorize('update', $record);
        $this->assertPatientAccessible($patient);

        $record = $this->recordService->updateRecord($record, $request->validated(), $request->user());

        return response()->json([
            'message' => __('workflow.messages.record_updated'),
            'data' => MedicalRecordResource::make($record),
        ]);
    }

    public function destroy(Patient $patient, MedicalRecord $record): JsonResponse
    {
        $this->authorize('delete', $record);
        $this->assertPatientAccessible($patient);

        $this->recordService->deleteRecord($record);

        return response()->json([
            'message' => __('workflow.messages.record_deleted'),
        ]);
    }

    public function electrodeTypes(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('viewAny', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        $companyId = $request->integer('implant_company_id') ?: null;
        $types = $companyId
            ? $this->lookupService->getImplantElectrodeTypes($companyId)
            : collect();

        return response()->json([
            'data' => $types->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
            ])->values(),
        ]);
    }

    public function followUpDefaults(Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        $template = $this->followUpDefaultService->templateForForm(auth()->user());

        if (! is_array($template)) {
            return response()->json(['has_defaults' => false]);
        }

        return response()->json(['has_defaults' => true, 'data' => $template]);
    }

    public function operationDefaults(Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        if (! $this->operationDefaultService->hasForUser(auth()->user())) {
            return response()->json(['has_defaults' => false]);
        }

        $data = $this->operationDefaultService->templateForForm(
            $this->operationDefaultService->getForUser(auth()->user())
        );

        return response()->json([
            'has_defaults' => true,
            'data' => OperationFieldSupport::payloadWithApplyLabels($data),
        ]);
    }

    public function preOperationDefaults(Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        $template = $this->preOperationDefaultService->templateForForm(auth()->user());

        if (! is_array($template)) {
            return response()->json(['has_defaults' => false]);
        }

        return response()->json(['has_defaults' => true, 'data' => $template]);
    }

    public function postOperationDefaults(Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        $template = $this->postOperationDefaultService->templateForForm(auth()->user());

        if (! is_array($template)) {
            return response()->json(['has_defaults' => false]);
        }

        return response()->json(['has_defaults' => true, 'data' => $template]);
    }

    public function campaignOperationDefaults(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        $validated = $request->validate([
            'implant_company_id' => ['required', 'integer', 'exists:implant_companies,id'],
        ]);

        $payload = $this->campaignOperationDefaultService->getForCampaignCompany(
            $patient->campaign,
            (int) $validated['implant_company_id']
        );

        if ($payload === null) {
            return response()->json(['has_defaults' => false]);
        }

        return response()->json([
            'has_defaults' => true,
            'data' => OperationFieldSupport::payloadWithApplyLabels($payload),
        ]);
    }

    public function operationQuickFill(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);
        $this->assertPatientAccessible($patient);

        $validated = $request->validate([
            'implant_company_id' => ['required', 'integer', 'exists:implant_companies,id'],
        ]);

        return response()->json([
            'presets' => $this->operationQuickFillService->presetsForCompany(
                (int) $validated['implant_company_id'],
                $request->user(),
                $patient->campaign,
            ),
        ]);
    }

    public function exportOperationPdf(Patient $patient, MedicalRecord $record): Response
    {
        $this->authorize('view', $record);
        $this->assertPatientAccessible($patient);

        return $this->operationReportPdfService->download($patient, $record);
    }

    /**
     * @param  array<string, \Illuminate\Support\Collection<int, mixed>>  $teamMembers
     * @return array<string, list<array{id: int, name: string}>>
     */
    private function formatTeamMembers(array $teamMembers): array
    {
        $formatted = [];

        foreach ($teamMembers as $role => $collection) {
            $formatted[$role] = $collection->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->full_name ?: trim($member->first_name.' '.$member->last_name),
            ])->values()->all();
        }

        return $formatted;
    }
}
