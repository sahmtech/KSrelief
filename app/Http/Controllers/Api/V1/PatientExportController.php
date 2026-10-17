<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\ExportPatientsRequest;
use App\Models\Campaign;
use App\Services\PatientAccessService;
use App\Services\PatientExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientExportController extends Controller
{
    public function __construct(
        private readonly PatientExportService $exportService,
        private readonly PatientAccessService $patientAccess,
    ) {}

    public function store(ExportPatientsRequest $request): BinaryFileResponse
    {
        $campaign = Campaign::query()->findOrFail((int) $request->input('campaign_id'));
        $this->patientAccess->assertCanAccessCampaign($request->user(), $campaign->id);

        return $this->exportService->download($campaign, $request->selectedStageCodes());
    }
}
