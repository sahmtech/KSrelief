<?php

namespace App\Http\Controllers;

use App\Http\Requests\Workflow\StoreMedicalRecordRequest;
use App\Http\Requests\Workflow\UpdateMedicalRecordRequest;
use App\Enums\SettingStatus;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\CampaignOperationDefaultService;
use App\Services\ClinicalSelectOptionService;
use App\Services\FollowUpFormDefaultService;
use App\Services\LookupService;
use App\Services\MedicalRecordService;
use App\Services\OperationFormDefaultService;
use App\Services\OperationQuickFillService;
use App\Services\OperationReportPdfService;
use App\Services\PatientService;
use App\Services\PostOperationFormDefaultService;
use App\Services\PreOperationFormDefaultService;
use App\Services\Settings\CtFindingOptionSettingService;
use App\Services\Settings\ExpectationPostCiOptionSettingService;
use App\Services\Settings\MriFindingOptionSettingService;
use App\Support\ClinicalCompositeFields;
use App\Support\OperationFieldSupport;
use App\Support\PostOperationFieldSupport;
use App\Support\PreOperationFieldSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MedicalRecordController extends Controller
{
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
        private readonly ClinicalSelectOptionService $clinicalSelectOptionService,
        private readonly CtFindingOptionSettingService $ctFindingOptionSettingService,
        private readonly MriFindingOptionSettingService $mriFindingOptionSettingService,
        private readonly ExpectationPostCiOptionSettingService $expectationPostCiOptionSettingService,
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
                'patient'     => $patient,
                'implantCompanies' => $formData['implantCompanies'],
                'insertionApproaches' => $formData['insertionApproaches'],
                'implantElectrodeTypes' => $formData['implantElectrodeTypes'],
                'insertionApproaches' => $formData['insertionApproaches'],
                'electrodeTypesUrl' => $formData['electrodeTypesUrl'],
                'enableFollowUpTemplateActions' => true,
                'hasFollowUpDefaults' => $formData['hasFollowUpDefaults'],
                'enableOperationTemplateActions' => true,
                'hasOperationDefaults' => $formData['hasOperationDefaults'],
                'enablePreOperationTemplateActions' => true,
                'hasPreOperationDefaults' => $formData['hasPreOperationDefaults'],
                'enablePostOperationTemplateActions' => true,
                'hasPostOperationDefaults' => $formData['hasPostOperationDefaults'],
                'hasCampaignOperationDefaults' => $formData['hasCampaignOperationDefaults'],
                'campaignOperationDefaultsUrl' => $formData['campaignOperationDefaultsUrl'],
                'operationQuickFillUrl' => $formData['operationQuickFillUrl'],
                'campaignDefaultCompanies' => $formData['campaignDefaultCompanies'],
                'stageFields' => $formData['stageFields'],
            ])->render(),
        ]);
    }

    public function electrodeTypes(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('viewAny', [MedicalRecord::class, $patient]);

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

    public function store(StoreMedicalRecordRequest $request, Patient $patient): RedirectResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $validated = $request->validated();
        $record = $this->recordService->createRecord($patient, $validated, $request->user());

        if ($request->hasFile('admission_attachments')) {
            foreach ($request->file('admission_attachments') as $file) {
                $this->patientService->uploadAttachment($patient, $file, $request->user());
            }
        }

        $stageCode = PatientStage::query()->find($validated['stage_id'])?->code;
        $successMessage = __('workflow.messages.record_created');

        if ($request->boolean('save_operation_defaults') && $stageCode === 'operation') {
            $this->operationDefaultService->saveFromRequestInput($request->user(), $request->all());
            $successMessage = __('workflow.operation.record_and_defaults_saved');
        }

        if ($request->boolean('save_follow_up_defaults') && $stageCode === 'follow_up') {
            $this->followUpDefaultService->saveFromRequestInput($request->user(), $request->all());
            $successMessage = __('workflow.follow_up.record_and_defaults_saved');
        }

        if ($request->boolean('save_pre_operation_defaults') && $stageCode === 'pre_operation') {
            $this->preOperationDefaultService->saveFromRequestInput($request->user(), $request->all());
            $successMessage = __('workflow.pre_op.record_and_defaults_saved');
        }

        if ($request->boolean('save_post_operation_defaults') && $stageCode === 'post_operation') {
            $this->postOperationDefaultService->saveFromRequestInput($request->user(), $request->all());
            $successMessage = __('workflow.post_op.record_and_defaults_saved');
        }

        if ($request->boolean('export_pdf') && $stageCode === 'operation') {
            return redirect()
                ->route('patients.records.export-operation-pdf', [$patient, $record])
                ->with('success', $successMessage);
        }

        return redirect()
            ->to(route('patients.show', $patient).'#records')
            ->with('success', $successMessage);
    }

    public function storeFollowUpDefaults(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $stage = PatientStage::query()->find($request->integer('stage_id'));

        if (($stage?->code ?? '') !== 'follow_up') {
            return redirect()
                ->route('patients.records.create', $patient)
                ->with('error', __('workflow.follow_up.defaults_stage_required'));
        }

        $this->followUpDefaultService->saveFromRequestInput($request->user(), $request->all());

        return redirect()
            ->route('patients.records.create', ['patient' => $patient, 'stage_id' => $stage->id])
            ->with('success', __('workflow.follow_up.defaults_saved'));
    }

    public function showFollowUpDefaults(Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $template = $this->followUpDefaultService->templateForForm(auth()->user());

        if (! is_array($template)) {
            return response()->json(['has_defaults' => false]);
        }

        return response()->json([
            'has_defaults' => true,
            'data' => $template,
        ]);
    }

    public function storePreOperationDefaults(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $stage = PatientStage::query()->find($request->integer('stage_id'));

        if (($stage?->code ?? '') !== 'pre_operation') {
            return redirect()
                ->route('patients.records.create', $patient)
                ->with('error', __('workflow.pre_op.defaults_stage_required'));
        }

        $this->preOperationDefaultService->saveFromRequestInput($request->user(), $request->all());

        return redirect()
            ->route('patients.records.create', ['patient' => $patient, 'stage_id' => $stage->id])
            ->with('success', __('workflow.pre_op.defaults_saved'));
    }

    public function showPreOperationDefaults(Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $template = $this->preOperationDefaultService->templateForForm(auth()->user());

        if (! is_array($template)) {
            return response()->json(['has_defaults' => false]);
        }

        return response()->json([
            'has_defaults' => true,
            'data' => $template,
        ]);
    }

    public function storePostOperationDefaults(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $stage = PatientStage::query()->find($request->integer('stage_id'));

        if (($stage?->code ?? '') !== 'post_operation') {
            return redirect()
                ->route('patients.records.create', $patient)
                ->with('error', __('workflow.post_op.defaults_stage_required'));
        }

        $this->postOperationDefaultService->saveFromRequestInput($request->user(), $request->all());

        return redirect()
            ->route('patients.records.create', ['patient' => $patient, 'stage_id' => $stage->id])
            ->with('success', __('workflow.post_op.defaults_saved'));
    }

    public function showPostOperationDefaults(Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $template = $this->postOperationDefaultService->templateForForm(auth()->user());

        if (! is_array($template)) {
            return response()->json(['has_defaults' => false]);
        }

        return response()->json([
            'has_defaults' => true,
            'data' => $template,
        ]);
    }

    public function show(Patient $patient, MedicalRecord $record): View
    {
        $this->authorize('view', $record);

        $record->load(['stage', 'submitter', 'specialty', 'patient.campaign']);

        $stageFields = $this->recordService->getStageFields($record->stage?->code ?? '');

        if (($record->stage?->code ?? '') === 'operation') {
            if (filled($record->field('electrode_type')) && ! isset($stageFields['electrode_type'])) {
                $stageFields['electrode_type'] = [
                    'label' => __('workflow.fields.electrode_type'),
                    'type' => 'text',
                ];
            }
            if (filled($record->field('insertion_type')) && ! isset($stageFields['insertion_approach_id'])) {
                $stageFields['insertion_type'] = [
                    'label' => __('workflow.fields.insertion_approach'),
                    'type' => 'text',
                ];
            }
            if (ClinicalCompositeFields::hasContent('clinical_aud', $record->field('clinical_aud'), [
                'type' => 'clinical_aud',
                'metrics_profile' => 'post_operation',
                'with_status' => false,
            ]) && ! isset($stageFields['clinical_aud'])) {
                $stageFields['clinical_aud'] = [
                    'label' => __('workflow.fields.clinical_aud'),
                    'type' => 'clinical_aud',
                    'metrics_profile' => 'post_operation',
                    'with_status' => false,
                ];
            }
        }

        return view('pages.patients.records.show', [
            'patient'     => $patient,
            'record'      => $record,
            'stageFields' => $stageFields,
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

        $validated = $request->validated();
        $record = $this->recordService->updateRecord($record, $validated, $request->user());

        if ($request->hasFile('admission_attachments')) {
            foreach ($request->file('admission_attachments') as $file) {
                $this->patientService->uploadAttachment($patient, $file, $request->user());
            }
        }

        $stageCode = PatientStage::query()->find($validated['stage_id'] ?? $record->stage_id)?->code
            ?? $record->stage?->code;

        if ($request->boolean('export_pdf') && $stageCode === 'operation') {
            return redirect()
                ->route('patients.records.export-operation-pdf', [$patient, $record])
                ->with('success', __('workflow.messages.record_updated'));
        }

        return redirect()
            ->to(route('patients.show', $patient).'#records')
            ->with('success', __('workflow.messages.record_updated'));
    }

    public function exportOperationPdf(Patient $patient, MedicalRecord $record): Response
    {
        $this->authorize('view', $record);

        $record->loadMissing('stage');

        abort_unless(
            $record->patient_id === $patient->id && ($record->stage?->code ?? '') === 'operation',
            404
        );

        return $this->operationReportPdfService->download($patient, $record);
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

        $allStages = PatientStage::query()->active()->ordered()->get();
        $hiddenStageCodes = config('patient_clinical.record_form_hidden_stage_codes', []);

        $stages = $allStages->reject(
            fn (PatientStage $stage): bool => in_array($stage->code, $hiddenStageCodes, true)
        )->values();

        $selectedStage = $stageId
            ? $allStages->firstWhere('id', $stageId)
            : ($record?->stage ?? $patient->currentStage);

        if ($record?->stage && $stages->doesntContain('id', $record->stage_id)) {
            $stages = $allStages
                ->where('id', $record->stage_id)
                ->merge($stages)
                ->sortBy('sort_order')
                ->values();
        }

        if (! $record && $selectedStage && in_array($selectedStage->code, $hiddenStageCodes, true)) {
            $selectedStage = $stages->first();
        }

        $stageCode = $selectedStage?->code ?? ($stages->first()?->code ?? 'pre_operation');
        $teamMembers = $this->lookupService->getCampaignTeamMembers($patient->campaign_id);

        $selectedCompanyId = old(
            'field_implant_company_id',
            $record?->field('implant_company_id')
        );

        return [
            'patient'     => $patient,
            'record'      => $record,
            'stages'      => $stages,
            'stageFields' => $this->recordService->getStageFields($stageCode),
            'stageCode'   => $stageCode,
            'teamMembers' => $teamMembers,
            'selectedStageId' => $selectedStage?->id,
            'implantCompanies' => $this->lookupService->getImplantCompanies(),
            'insertionApproaches' => $this->lookupService->getInsertionApproaches(),
            'implantElectrodeTypes' => $selectedCompanyId
                ? $this->lookupService->getImplantElectrodeTypes((int) $selectedCompanyId)
                : collect(),
            'electrodeTypesUrl' => route('patients.records.electrode-types', $patient),
            'hasFollowUpDefaults' => $this->followUpDefaultService->hasForUser(auth()->user()),
            'followUpDefaultsUrl' => route('patients.records.follow-up-defaults.show', $patient),
            'hasOperationDefaults' => $this->operationDefaultService->hasForUser(auth()->user()),
            'operationDefaultsUrl' => route('patients.records.operation-defaults.show', $patient),
            'hasPreOperationDefaults' => $this->preOperationDefaultService->hasForUser(auth()->user()),
            'preOperationDefaultsUrl' => route('patients.records.pre-operation-defaults.show', $patient),
            'hasPostOperationDefaults' => $this->postOperationDefaultService->hasForUser(auth()->user()),
            'postOperationDefaultsUrl' => route('patients.records.post-operation-defaults.show', $patient),
            'hasCampaignOperationDefaults' => $this->campaignOperationDefaultService->hasForCampaign($patient->campaign),
            'campaignOperationDefaultsUrl' => route('patients.records.campaign-operation-defaults.show', $patient),
            'operationQuickFillUrl' => route('patients.records.operation-quick-fill', $patient),
            'campaignDefaultCompanies' => $this->campaignOperationDefaultService->supportedCompanies(),
        ];
    }

    public function showOperationQuickFill(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

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

    public function storeOperationDefaults(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $stage = PatientStage::query()->find($request->integer('stage_id'));

        if (($stage?->code ?? '') !== 'operation') {
            return redirect()
                ->route('patients.records.create', $patient)
                ->with('error', __('workflow.operation.defaults_stage_required'));
        }

        $this->operationDefaultService->saveFromRequestInput($request->user(), $request->all());

        return redirect()
            ->route('patients.records.create', ['patient' => $patient, 'stage_id' => $stage->id])
            ->with('success', __('workflow.operation.defaults_saved'));
    }

    public function showOperationDefaults(Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

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

    public function showCampaignOperationDefaults(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $patient->loadMissing('campaign');

        if (! $patient->campaign) {
            return response()->json(['has_defaults' => false]);
        }

        $validated = $request->validate([
            'implant_company_id' => ['required', 'integer', 'exists:implant_companies,id'],
        ]);

        $defaults = $this->campaignOperationDefaultService->getForCampaignCompany(
            $patient->campaign,
            (int) $validated['implant_company_id']
        );

        if ($defaults === null) {
            return response()->json(['has_defaults' => false]);
        }

        $data = $this->campaignOperationDefaultService->templateForForm($defaults);

        return response()->json([
            'has_defaults' => true,
            'data' => OperationFieldSupport::payloadWithApplyLabels($data),
        ]);
    }

    public function storeClinicalSelectOption(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        if (! array_key_exists($validated['category'], PreOperationFieldSupport::SELECT_CATEGORY_CONFIG)
            && ! array_key_exists($validated['category'], OperationFieldSupport::SELECT_CATEGORY_CONFIG)
            && ! array_key_exists($validated['category'], PostOperationFieldSupport::SELECT_CATEGORY_CONFIG)) {
            return response()->json(['message' => __('workflow.pre_op.invalid_option_category')], 422);
        }

        $option = $this->clinicalSelectOptionService->createForCategory(
            $validated['category'],
            $validated['name'],
            $request->user()
        );

        return response()->json([
            'code' => $option->code,
            'label' => $option->name,
        ]);
    }

    public function storeCtFindingOption(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $option = $this->ctFindingOptionSettingService->create([
            'name' => $validated['name'],
            'code' => $this->ctFindingOptionSettingService->generateUniqueCode($validated['name']),
            'status' => SettingStatus::Active->value,
        ], $request->user()->id);

        return response()->json([
            'id' => $option->id,
            'label' => $option->name,
        ]);
    }

    public function storeMriFindingOption(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $option = $this->mriFindingOptionSettingService->create([
            'name' => $validated['name'],
            'code' => $this->mriFindingOptionSettingService->generateUniqueCode($validated['name']),
            'status' => SettingStatus::Active->value,
        ], $request->user()->id);

        return response()->json([
            'id' => $option->id,
            'label' => $option->name,
        ]);
    }

    public function storeExpectationPostCiOption(Request $request, Patient $patient): JsonResponse
    {
        $this->authorize('create', [MedicalRecord::class, $patient]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $option = $this->expectationPostCiOptionSettingService->create([
            'name' => $validated['name'],
            'code' => $this->expectationPostCiOptionSettingService->generateUniqueCode($validated['name']),
            'status' => SettingStatus::Active->value,
        ], $request->user()->id);

        return response()->json([
            'id' => $option->id,
            'label' => $option->name,
        ]);
    }
}
