<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PatientImportBatchStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\ApprovePatientImportRequest;
use App\Http\Requests\Patient\UploadPatientImportRequest;
use App\Http\Resources\PatientImportBatchResource;
use App\Http\Resources\PatientImportLogResource;
use App\Models\Campaign;
use App\Models\PatientImportBatch;
use App\Services\PatientAccessService;
use App\Services\PatientImportService;
use App\Services\PatientImportStatisticsService;
use App\Support\PatientImportSpec;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PatientImportController extends Controller
{
    public function __construct(
        private readonly PatientImportService $importService,
        private readonly PatientImportStatisticsService $statisticsService,
        private readonly PatientAccessService $patientAccess,
    ) {}

    public function meta(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->can('patient.import_excel') || $request->user()?->can('patient.import_history'),
            403
        );

        return response()->json([
            'spec' => PatientImportSpec::toArray(),
            'template_download_url' => route('api.v1.patients.import.template'),
        ]);
    }

    public function template(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('patient.import_excel'), 403);

        return Excel::download(
            new \App\Exports\PatientTemplateExport,
            'patient-import-template.xlsx'
        );
    }

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('patient.import_history'), 403);

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = PatientImportBatch::query()
            ->with(['campaign', 'importer', 'approver'])
            ->orderByDesc('created_at');

        $this->scopeBatchesForUser($query, $request);

        if ($campaignId = $request->query('campaign_id')) {
            $query->where('campaign_id', (int) $campaignId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $batches = $query->paginate($perPage);

        return response()->json([
            'data' => PatientImportBatchResource::collection($batches),
            'meta' => [
                'current_page' => $batches->currentPage(),
                'last_page' => $batches->lastPage(),
                'per_page' => $batches->perPage(),
                'total' => $batches->total(),
            ],
            'stats' => $this->statisticsService->getStats(),
        ]);
    }

    public function store(UploadPatientImportRequest $request): JsonResponse
    {
        $campaignId = (int) $request->input('campaign_id');
        $this->assertCampaignAccessible($request, $campaignId);

        $batch = $this->importService->uploadFile(
            $request->file('file'),
            $request->user(),
            $campaignId,
            $request->input('notes')
        );

        $batch->refresh()->load(['campaign', 'importer']);

        if ($batch->status === PatientImportBatchStatus::Failed) {
            return response()->json([
                'message' => __('patients.import.messages.validation_failed_title'),
                'error' => $batch->failure_reason,
                'batch' => PatientImportBatchResource::make($batch),
            ], 422);
        }

        $statusCode = $batch->status === PatientImportBatchStatus::Review ? 201 : 202;

        return response()->json([
            'message' => __('patients.import.messages.uploaded'),
            'batch' => PatientImportBatchResource::make($batch),
        ], $statusCode);
    }

    public function show(Request $request, PatientImportBatch $batch): JsonResponse
    {
        abort_unless($request->user()?->can('patient.import_history'), 403);
        $this->assertBatchAccessible($request, $batch);

        $batch->load(['campaign', 'importer', 'approver']);

        $perPage = min(max((int) $request->query('per_page', 50), 1), 200);

        $logs = $batch->logs()
            ->orderBy('row_number')
            ->paginate($perPage, ['*'], 'log_page');

        $payload = [
            'batch' => PatientImportBatchResource::make($batch),
            'logs' => PatientImportLogResource::collection($logs),
            'logs_meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ];

        if (in_array($batch->status?->value, ['uploaded', 'processing'], true)) {
            $payload['polling'] = [
                'recommended_interval_seconds' => 5,
                'message' => $batch->status === PatientImportBatchStatus::Uploaded
                    ? __('patients.import.messages.queued_wait')
                    : __('patients.import.messages.processing_wait'),
            ];
        }

        return response()->json($payload);
    }

    public function approve(ApprovePatientImportRequest $request, PatientImportBatch $batch): JsonResponse
    {
        $this->assertBatchAccessible($request, $batch);

        try {
            $imported = $this->importService->approveImport($batch, $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $batch->refresh()->load(['campaign', 'importer', 'approver']);

        return response()->json([
            'message' => __('patients.import.messages.approved', ['count' => $imported]),
            'imported_count' => $imported,
            'batch' => PatientImportBatchResource::make($batch),
        ]);
    }

    public function downloadErrors(Request $request, PatientImportBatch $batch): mixed
    {
        abort_unless($request->user()?->can('patient.import_history'), 403);
        $this->assertBatchAccessible($request, $batch);

        $path = $this->importService->generateErrorFile($batch);

        return Storage::disk('local')->download(
            $path,
            'import-errors-batch-'.$batch->id.'.xlsx'
        );
    }

    private function assertCampaignAccessible(Request $request, int $campaignId): void
    {
        Campaign::query()->findOrFail($campaignId);

        $allowed = $this->patientAccess->allowedCampaignIds($request->user());

        if ($allowed !== null && ! in_array($campaignId, $allowed, true)) {
            abort(403, __('patients.api.messages.patient_not_in_campaign'));
        }
    }

    private function assertBatchAccessible(Request $request, PatientImportBatch $batch): void
    {
        if (! $batch->campaign_id) {
            return;
        }

        $this->assertCampaignAccessible($request, (int) $batch->campaign_id);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<PatientImportBatch>  $query
     */
    private function scopeBatchesForUser($query, Request $request): void
    {
        $allowed = $this->patientAccess->allowedCampaignIds($request->user());

        if ($allowed !== null) {
            if ($allowed === []) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereIn('campaign_id', $allowed);
        }
    }
}
