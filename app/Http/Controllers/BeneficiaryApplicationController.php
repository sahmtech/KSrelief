<?php

namespace App\Http\Controllers;

use App\Enums\BeneficiaryApplicationStatus;
use App\Models\BeneficiaryApplication;
use App\Models\BeneficiaryApplicationAttachment;
use App\Services\BeneficiaryApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BeneficiaryApplicationController extends Controller
{
    public function __construct(
        private readonly BeneficiaryApplicationService $applicationService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BeneficiaryApplication::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
            'extra_type' => $request->query('extra_type'),
        ];

        $query = BeneficiaryApplication::query()->with(['reviewer', 'acceptedCampaign']);

        if (filled($filters['search'])) {
            $search = '%'.trim((string) $filters['search']).'%';
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', $search)
                    ->orWhere('national_number', 'like', $search)
                    ->orWhere('mobile', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('uuid', 'like', $search);
            });
        }

        if (filled($filters['status']) && in_array($filters['status'], BeneficiaryApplicationStatus::values(), true)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['extra_type'])) {
            $query->where('extra_type', (int) $filters['extra_type']);
        }

        $applications = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('pages.beneficiary-applications.index', [
            'applications' => $applications,
            'filters' => $filters,
            'meta' => $this->applicationService->formMeta(),
            'stats' => [
                'pending' => BeneficiaryApplication::query()->pending()->count(),
                'accepted' => BeneficiaryApplication::query()->where('status', BeneficiaryApplicationStatus::Accepted)->count(),
                'rejected' => BeneficiaryApplication::query()->where('status', BeneficiaryApplicationStatus::Rejected)->count(),
                'total' => BeneficiaryApplication::query()->count(),
            ],
        ]);
    }

    public function show(BeneficiaryApplication $beneficiaryApplication): View
    {
        $this->authorize('view', $beneficiaryApplication);

        $beneficiaryApplication->load([
            'attachments',
            'reviewer',
            'acceptedPatient',
            'acceptedCampaign',
        ]);

        return view('pages.beneficiary-applications.show', [
            'application' => $beneficiaryApplication,
            'campaigns' => $this->applicationService->campaignsForReviewer(auth()->user()),
            'meta' => $this->applicationService->formMeta(),
        ]);
    }

    public function accept(Request $request, BeneficiaryApplication $beneficiaryApplication): RedirectResponse
    {
        $this->authorize('review', $beneficiaryApplication);

        $validated = $request->validate([
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
        ], [
            'campaign_id.required' => __('beneficiary_registration.messages.campaign_required'),
        ]);

        $patient = $this->applicationService->accept(
            $beneficiaryApplication,
            $request->user(),
            (int) $validated['campaign_id'],
        );

        return redirect()
            ->route('beneficiary-applications.show', $beneficiaryApplication)
            ->with('success', __('beneficiary_registration.messages.accepted'))
            ->with('accepted_patient_id', $patient->id);
    }

    public function reject(Request $request, BeneficiaryApplication $beneficiaryApplication): RedirectResponse
    {
        $this->authorize('review', $beneficiaryApplication);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ], [
            'rejection_reason.required' => __('beneficiary_registration.messages.reason_required'),
        ]);

        $this->applicationService->reject(
            $beneficiaryApplication,
            $request->user(),
            $validated['rejection_reason'],
        );

        return redirect()
            ->route('beneficiary-applications.show', $beneficiaryApplication)
            ->with('success', __('beneficiary_registration.messages.rejected'));
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
