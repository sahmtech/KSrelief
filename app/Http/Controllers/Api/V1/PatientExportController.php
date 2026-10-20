<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\ApiExportPatientsRequest;
use App\Models\Campaign;
use App\Models\Patient;
use App\Services\PatientAccessService;
use App\Services\PatientExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientExportController extends Controller
{
    public function __construct(
        private readonly PatientExportService $exportService,
        private readonly PatientAccessService $patientAccess,
    ) {}

    public function meta(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('export', Patient::class), 403);

        $user = $request->user();
        $campaigns = $this->patientAccess->exportableCampaigns($user);

        return response()->json([
            'campaigns' => $campaigns->map(fn (Campaign $campaign) => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'code' => $campaign->code,
            ])->values(),
            'exportable_stages' => $this->exportService->exportableStages()->map(fn ($stage) => [
                'code' => $stage->code,
                'name' => $stage->displayName(),
            ])->values(),
            'defaults' => [
                'campaign_id' => $this->patientAccess->defaultExportCampaignId($user),
                'include_all_stages' => true,
            ],
            'export_url' => route('api.v1.patients.export'),
        ]);
    }

    public function store(ApiExportPatientsRequest $request): BinaryFileResponse
    {
        $campaign = Campaign::query()->findOrFail((int) $request->input('campaign_id'));
        $this->patientAccess->assertCanAccessCampaign($request->user(), $campaign->id);

        return $this->exportService->download($campaign, $request->selectedStageCodes());
    }
}
