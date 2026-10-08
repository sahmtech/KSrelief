<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\LookupService;
use App\Services\MedicalRecordService;
use App\Services\PatientAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BootstrapController extends Controller
{
    public function __construct(
        private readonly LookupService $lookupService,
        private readonly MedicalRecordService $recordService,
        private readonly PatientAccessService $patientAccess,
    ) {}

    public function patientsModule(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('patient.view'), 403);

        $campaignQuery = Campaign::query()->orderBy('name');
        $allowed = $this->patientAccess->allowedCampaignIds($request->user());

        if ($allowed !== null) {
            $campaignQuery->whereIn('id', $allowed);
        }

        return response()->json([
            'campaigns' => $campaignQuery->get(['id', 'name', 'code']),
            'patient_stages' => $this->lookupService->getPatientStages(),
            'eligibility_statuses' => $this->lookupService->getPatientEligibilityStatuses(),
            'genders' => collect(Gender::cases())->map(fn (Gender $g) => [
                'value' => $g->value,
                'label' => $g->label(),
            ]),
            'admission_statuses' => collect(AdmissionStatus::cases())->map(fn (AdmissionStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
            'screening_field_definitions' => $this->recordService->getScreeningFields(),
            'clinical_phases' => $this->recordService->clinicalPhases(),
            'record_form_hidden_stage_codes' => config('patient_clinical.record_form_hidden_stage_codes', []),
            'patient_visibility' => $this->patientAccess->visibilityMeta($request->user()),
        ]);
    }
}
