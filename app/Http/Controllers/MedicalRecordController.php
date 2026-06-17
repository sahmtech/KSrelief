<?php

namespace App\Http\Controllers;

use App\Http\Requests\Workflow\StoreMedicalRecordRequest;
use App\Http\Requests\Workflow\UpdateMedicalRecordRequest;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\LookupService;
use App\Services\MedicalRecordService;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    public function __construct(
        private readonly MedicalRecordService $recordService,
        private readonly LookupService $lookupService,
        private readonly PatientService $patientService,
    ) {}

    public function index(Patient $patient): View
    {
        $this->authorize('viewAny', [MedicalRecord::class, $patient]);

        $patient->load('currentStage');

        return view('pages.patients.records.index', [
            'patient' => $patient,
            'records' => $this->recordService->getPatientRecords($patient),
        ]);
    }

    public function create(Request $request, Patient $patient): View
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $formData = $this->recordFormData($patient, $request->integer('stage_id') ?: null);

        return view('pages.patients.records.create', $formData);
    }

    public function stageFields(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $formData = $this->recordFormData($patient, $request->integer('stage_id') ?: null);

        return response()->json([
            'html' => view('pages.patients.records._stage_fields', [
                'stageFields' => $formData['stageFields'],
                'stageCode'   => $formData['stageCode'],
                'record'      => null,
                'teamMembers' => $formData['teamMembers'],
            ])->render(),
        ]);
    }

    public function store(StoreMedicalRecordRequest $request, Patient $patient): RedirectResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $this->recordService->createRecord($patient, $request->validated(), $request->user());

        if ($request->hasFile('admission_attachments')) {
            foreach ($request->file('admission_attachments') as $file) {
                $this->patientService->uploadAttachment($patient, $file, $request->user());
            }
        }

        return redirect()
            ->to(route('patients.show', $patient).'#records')
            ->with('success', __('workflow.messages.record_created'));
    }

    public function show(Patient $patient, MedicalRecord $record): View
    {
        $this->authorize('view', $record);

        $record->load(['stage', 'submitter', 'specialty', 'patient.campaign']);

        return view('pages.patients.records.show', [
            'patient'     => $patient,
            'record'      => $record,
            'stageFields' => $this->recordService->getStageFields($record->stage?->code ?? ''),
            'teamMembers' => $this->lookupService->getCampaignTeamMembers($patient->campaign_id),
        ]);
    }

    public function edit(Request $request, Patient $patient, MedicalRecord $record): View
    {
        $this->authorize('update', $record);

        $stageId = $request->integer('stage_id') ?: $record->stage_id;
        $formData = $this->recordFormData($patient, $stageId, $record);

        return view('pages.patients.records.edit', $formData);
    }

    public function update(UpdateMedicalRecordRequest $request, Patient $patient, MedicalRecord $record): RedirectResponse
    {
        $this->authorize('update', $record);

        $this->recordService->updateRecord($record, $request->validated(), $request->user());

        if ($request->hasFile('admission_attachments')) {
            foreach ($request->file('admission_attachments') as $file) {
                $this->patientService->uploadAttachment($patient, $file, $request->user());
            }
        }

        return redirect()
            ->to(route('patients.show', $patient).'#records')
            ->with('success', __('workflow.messages.record_updated'));
    }

    public function destroy(Patient $patient, MedicalRecord $record): RedirectResponse
    {
        $this->authorize('delete', $record);

        $this->recordService->deleteRecord($record);

        return redirect()
            ->to(route('patients.show', $patient).'#records')
            ->with('success', __('workflow.messages.record_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function recordFormData(Patient $patient, ?int $stageId = null, ?MedicalRecord $record = null): array
    {
        $patient->load(['currentStage', 'campaign']);

        $stages = PatientStage::query()->active()->ordered()->get();
        $selectedStage = $stageId
            ? $stages->firstWhere('id', $stageId)
            : ($record?->stage ?? $patient->currentStage);
        $stageCode = $selectedStage?->code ?? 'admission';
        $teamMembers = $this->lookupService->getCampaignTeamMembers($patient->campaign_id);

        return [
            'patient'     => $patient,
            'record'      => $record,
            'stages'      => $stages,
            'stageFields' => $this->recordService->getStageFields($stageCode),
            'stageCode'   => $stageCode,
            'teamMembers' => $teamMembers,
            'selectedStageId' => $selectedStage?->id,
        ];
    }
}
