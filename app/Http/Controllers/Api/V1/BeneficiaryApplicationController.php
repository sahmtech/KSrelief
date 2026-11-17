<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BeneficiaryApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BeneficiaryApplicationResource;
use App\Http\Resources\PatientResource;
use App\Models\BeneficiaryApplication;
use App\Models\BeneficiaryApplicationAttachment;
use App\Services\BeneficiaryApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BeneficiaryApplicationController extends Controller
{
    public function __construct(
        private readonly BeneficiaryApplicationService $applicationService,
    ) {}

    public function meta(): JsonResponse
    {
        $this->authorize('viewAny', BeneficiaryApplication::class);

        return response()->json([
            'data' => array_merge(
                $this->applicationService->formMeta(),
                [
                    'campaigns' => $this->applicationService->campaignsForReviewer(auth()->user()),
                ]
            ),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BeneficiaryApplication::class);

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $query = BeneficiaryApplication::query()
            ->with(['reviewer', 'acceptedCampaign']);

        if (filled($request->query('search'))) {
            $search = '%'.trim((string) $request->query('search')).'%';
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', $search)
                    ->orWhere('national_number', 'like', $search)
                    ->orWhere('mobile', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('uuid', 'like', $search);
            });
        }

        if (filled($request->query('status')) && in_array($request->query('status'), BeneficiaryApplicationStatus::values(), true)) {
            $query->where('status', $request->query('status'));
        }

        if (filled($request->query('extra_type'))) {
            $query->where('extra_type', (int) $request->query('extra_type'));
        }

        $applications = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => BeneficiaryApplicationResource::collection($applications),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    public function show(BeneficiaryApplication $beneficiaryApplication): JsonResponse
    {
        $this->authorize('view', $beneficiaryApplication);

        $beneficiaryApplication->load([
            'attachments',
            'reviewer',
            'acceptedCampaign',
            'acceptedPatient',
        ]);

        return response()->json([
            'data' => (new BeneficiaryApplicationResource($beneficiaryApplication))->resolve(),
        ]);
    }

    public function accept(Request $request, BeneficiaryApplication $beneficiaryApplication): JsonResponse
    {
        $this->authorize('review', $beneficiaryApplication);

        $validated = $request->validate([
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
        ]);

        $patient = $this->applicationService->accept(
            $beneficiaryApplication,
            $request->user(),
            (int) $validated['campaign_id'],
        );

        return response()->json([
            'message' => __('beneficiary_registration.messages.accepted'),
            'data' => [
                'application' => new BeneficiaryApplicationResource(
                    $beneficiaryApplication->fresh(['reviewer', 'acceptedCampaign', 'attachments'])
                ),
                'patient' => PatientResource::make($patient),
            ],
        ]);
    }

    public function reject(Request $request, BeneficiaryApplication $beneficiaryApplication): JsonResponse
    {
        $this->authorize('review', $beneficiaryApplication);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $application = $this->applicationService->reject(
            $beneficiaryApplication,
            $request->user(),
            $validated['rejection_reason'],
        );

        return response()->json([
            'message' => __('beneficiary_registration.messages.rejected'),
            'data' => new BeneficiaryApplicationResource($application),
        ]);
    }

    public function downloadAttachment(
        BeneficiaryApplication $beneficiaryApplication,
        BeneficiaryApplicationAttachment $attachment,
    ): StreamedResponse {
        $this->authorize('view', $beneficiaryApplication);

        abort_unless(
            (int) $attachment->beneficiary_application_id === (int) $beneficiaryApplication->id,
            404
        );
        abort_unless($attachment->existsOnDisk(), 404);

        return Storage::disk($attachment->disk())->download(
            $attachment->path,
            $attachment->original_name
        );
    }
}
