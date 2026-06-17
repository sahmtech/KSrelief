<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\ApprovePatientImportRequest;
use App\Http\Requests\Patient\UploadPatientImportRequest;
use App\Models\Campaign;
use App\Models\PatientImportBatch;
use App\Services\LookupService;
use App\Services\PatientImportService;
use App\Services\PatientImportStatisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PatientImportController extends Controller
{
    public function __construct(
        private readonly PatientImportService $importService,
        private readonly PatientImportStatisticsService $statisticsService,
        private readonly LookupService $lookupService
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('patient.import_history'), 403);

        $batches = PatientImportBatch::query()
            ->with(['campaign', 'importer', 'approver'])
            ->orderByDesc('created_at')
            ->paginate(25);

        $stats = $this->statisticsService->getStats();

        return view('pages.patients.import.index', [
            'batches' => $batches,
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('patient.import_excel'), 403);

        $campaign = $request->query('campaign_id')
            ? Campaign::query()->find($request->query('campaign_id'))
            : null;

        return view('pages.patients.import.create', [
            'campaigns' => Campaign::query()->orderBy('name')->get(['id', 'name', 'code']),
            'selectedCampaign' => $campaign,
            'eligibilityStatuses' => $this->lookupService->getPatientEligibilityStatuses(),
            'patientStages' => $this->lookupService->getPatientStages(),
        ]);
    }

    public function store(UploadPatientImportRequest $request): RedirectResponse
    {
        $batch = $this->importService->uploadFile(
            $request->file('file'),
            $request->user(),
            $request->input('campaign_id') ? (int) $request->input('campaign_id') : null,
            $request->input('notes')
        );

        return redirect()
            ->route('patients.import.show', $batch)
            ->with('info', __('patients.import.messages.uploaded'));
    }

    public function show(Request $request, PatientImportBatch $batch): View
    {
        abort_unless($request->user()?->can('patient.import_history'), 403);

        $batch->load(['campaign', 'importer', 'approver']);

        $logs = $batch->logs()
            ->orderBy('row_number')
            ->paginate(50, ['*'], 'log_page');

        $canApprove = $request->user()?->can('patient.import_approve')
            && $batch->status?->isApprovable();

        return view('pages.patients.import.show', [
            'batch' => $batch,
            'logs' => $logs,
            'canApprove' => $canApprove,
        ]);
    }

    public function approve(ApprovePatientImportRequest $request, PatientImportBatch $batch): RedirectResponse
    {
        try {
            $imported = $this->importService->approveImport($batch, $request->user());

            return redirect()
                ->route('patients.import.show', $batch)
                ->with('success', __('patients.import.messages.approved', ['count' => $imported]));
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function downloadErrors(Request $request, PatientImportBatch $batch): mixed
    {
        abort_unless($request->user()?->can('patient.import_history'), 403);

        $path = $this->importService->generateErrorFile($batch);

        return Storage::disk('local')->download(
            $path,
            'import-errors-batch-'.$batch->id.'.xlsx'
        );
    }

    public function downloadTemplate(Request $request): mixed
    {
        abort_unless($request->user()?->can('patient.import_excel'), 403);

        $campaignCode = 'CAMP-0001';

        if ($request->query('campaign_id')) {
            $campaign = Campaign::query()->find($request->query('campaign_id'));
            if ($campaign?->code) {
                $campaignCode = $campaign->code;
            }
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\PatientTemplateExport($campaignCode),
            'patient-import-template.xlsx'
        );
    }
}
