<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\ExportPatientsRequest;
use App\Models\Campaign;
use App\Models\Patient;
use App\Services\PatientExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientExportController extends Controller
{
    public function __construct(
        private readonly PatientExportService $exportService,
    ) {}

    public function create(Request $request): View
    {
        $this->authorize('export', Patient::class);

        $campaign = $request->query('campaign_id')
            ? Campaign::query()->find($request->query('campaign_id'))
            : null;

        return view('pages.patients.export.create', [
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name', 'code']),
            'selectedCampaign' => $campaign,
            'exportableStages' => $this->exportService->exportableStages(),
        ]);
    }

    public function store(ExportPatientsRequest $request): BinaryFileResponse|RedirectResponse
    {
        $campaign = Campaign::query()->findOrFail((int) $request->input('campaign_id'));

        try {
            return $this->exportService->download($campaign, $request->selectedStageCodes());
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', __('patients.export.messages.failed'));
        }
    }
}
