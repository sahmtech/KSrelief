<?php

namespace App\Http\Controllers;

use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Enums\PatientRecordStatus;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Models\Campaign;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\LookupService;
use App\Services\MedicalRecordService;
use App\Services\PatientBriefService;
use App\Services\PatientClinicalProfileService;
use App\Services\PatientService;
use App\Services\PatientStatisticsService;
use App\Services\PatientWorkflowService;
use App\Services\ActivityStatisticsService;
use App\Services\TransportationStatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientService $patientService,
        private readonly PatientStatisticsService $statisticsService,
        private readonly LookupService $lookupService,
        private readonly PatientWorkflowService $workflowService,
        private readonly MedicalRecordService $recordService,
        private readonly TransportationStatisticsService $transportationStatisticsService,
        private readonly ActivityStatisticsService $activityStatisticsService,
        private readonly PatientBriefService $briefService,
    ) {}

    public function index(Request $request): View
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

        $patients = Patient::query()
            ->with(['campaign', 'eligibilityStatus', 'currentStage'])
            ->search($filters['search'])
            ->filter($filters)
            ->orderByDesc('created_at')
            ->get();

        return view('pages.patients.index', [
            'patients' => $patients,
            'stats' => $this->statisticsService->getPatientCounts(),
            'filters' => $filters,
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name']),
            'eligibilityStatuses' => $this->lookupService->getPatientEligibilityStatuses(),
            'patientStages' => $this->lookupService->getPatientStages(),
            'genders' => Gender::cases(),
            'admissionStatuses' => AdmissionStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Patient::class);

        return view('pages.patients.create', $this->formData($request->query('campaign_id')));
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $patient = $this->patientService->createPatient(
            $request->safe()->except(['attachments', 'photo']),
            $request->user(),
            $request->file('attachments', []),
            $request->file('photo')
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', __('patients.messages.created'));
    }

    public function brief(Patient $patient): View
    {
        $this->authorize('view', $patient);

        $user = auth()->user();

        $patient->load([
            'campaign.country',
            'campaign.city',
            'eligibilityStatus',
            'currentStage',
            'attachments' => fn ($query) => $query->with('uploader')->latest(),
        ]);

        $clinicalProfile = $user->can('viewAny', [MedicalRecord::class, $patient])
            ? app(PatientClinicalProfileService::class)->buildProfile($patient)
            : null;

        return view('pages.patients.brief', [
            'patient' => $patient,
            'brief' => $this->briefService->build($patient, $clinicalProfile),
            'clinicalProfile' => $clinicalProfile,
        ]);
    }

    public function show(Patient $patient): View
    {
        $this->authorize('view', $patient);

        $user = auth()->user();

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
            ]);
        }

        $patient->load($relations);

        $workflowTimeline = $user->can('viewWorkflow', $patient)
            ? $this->workflowService->getTimeline($patient)
            : [];

        $workflowStages = $user->can('viewWorkflow', $patient)
            ? PatientStage::query()->active()->ordered()->get()
            : collect();

        $stageHistory = $user->can('viewStageHistory', $patient)
            ? $patient->stageHistories->sortByDesc('changed_at')
            : collect();

        $medicalRecords = $user->can('viewAny', [MedicalRecord::class, $patient])
            ? $patient->medicalRecords->sortByDesc('record_date')
            : collect();

        $transportStats = ['total' => 0, 'upcoming' => 0, 'completed' => 0];
        $patientTrips = collect();

        if ($user->can('transportation.view')) {
            $transportStats = $this->transportationStatisticsService->getPatientTransportStats($patient->id);
            $patientTrips = $this->transportationStatisticsService->getPatientTrips($patient->id);
        }

        $activityStats = ['total' => 0, 'upcoming' => 0, 'completed' => 0, 'attended' => 0];
        $patientActivities = collect();

        if ($user->can('activity.view')) {
            $activityStats = $this->activityStatisticsService->getParticipantStats(patientId: $patient->id);
            $patientActivities = $this->activityStatisticsService->getPatientActivities($patient->id);
        }

        return view('pages.patients.show', [
            'patient'          => $patient,
            'workflowTimeline' => $workflowTimeline,
            'workflowStages'   => $workflowStages,
            'stageHistory'     => $stageHistory,
            'medicalRecords'   => $medicalRecords,
            'clinicalProfile'  => $user->can('viewAny', [MedicalRecord::class, $patient])
                ? app(PatientClinicalProfileService::class)->buildProfile($patient)
                : null,
            'screeningFields'  => $this->recordService->getScreeningFields(),
            'clinicalPhases'   => $this->recordService->clinicalPhases(),
            'transportStats'   => $transportStats,
            'patientTrips'     => $patientTrips,
            'activityStats'    => $activityStats,
            'patientActivities'=> $patientActivities,
        ]);
    }

    public function edit(Patient $patient): View
    {
        $this->authorize('update', $patient);

        $patient->load(['campaign', 'eligibilityStatus', 'currentStage', 'attachments']);

        return view('pages.patients.edit', [
            'patient' => $patient,
            ...$this->formData($patient->campaign_id),
        ]);
    }

    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $this->patientService->updatePatient(
            $patient,
            $request->safe()->except(['attachments', 'photo', 'remove_photo']),
            $request->user(),
            $request->file('attachments', []),
            $request->file('photo'),
            $request->boolean('remove_photo')
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', __('patients.messages.updated'));
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        $this->authorize('delete', $patient);

        $this->patientService->deletePatient($patient);

        return redirect()
            ->route('patients.index')
            ->with('success', __('patients.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?int $selectedCampaignId = null): array
    {
        return [
            'campaigns' => Campaign::query()->with('country')->orderBy('name')->get(),
            'selectedCampaignId' => $selectedCampaignId,
            'eligibilityStatuses' => $this->lookupService->getPatientEligibilityStatuses(),
            'patientStages' => $this->lookupService->getPatientStages(),
            'genders' => Gender::cases(),
            'admissionStatuses' => AdmissionStatus::cases(),
            'recordStatuses' => PatientRecordStatus::cases(),
            'screeningFields' => $this->recordService->getScreeningFields(),
            'clinicalPhases' => $this->recordService->clinicalPhases(),
            'surgicalSides' => ['left', 'right', 'bilateral'],
        ];
    }
}
