<?php

namespace App\Http\Controllers;

use App\Http\Requests\Beneficiary\StoreBeneficiaryApplicationRequest;
use App\Models\BeneficiaryApplication;
use App\Services\BeneficiaryApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BeneficiaryRegistrationController extends Controller
{
    public function __construct(
        private readonly BeneficiaryApplicationService $applicationService,
    ) {}

    public function create(): View
    {
        return view('pages.beneficiary-registration.create', [
            'meta' => $this->applicationService->formMeta(),
        ]);
    }

    public function store(StoreBeneficiaryApplicationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $filesByField = [];

        if ($request->file('photo')) {
            $filesByField['photo'] = [$request->file('photo')];
        }

        if ($request->file('extra.medical_reports')) {
            $filesByField['medical_reports'] = $request->file('extra.medical_reports');
        }

        if ($request->file('health.health_medical_records')) {
            $filesByField['health_medical_records'] = $request->file('health.health_medical_records');
        }

        foreach (['profiles_national_images', 'profiles_family_cards', 'profiles_attachments'] as $key) {
            if ($request->file($key)) {
                $filesByField[$key] = $request->file($key);
            }
        }

        $application = $this->applicationService->submit(
            $validated,
            $filesByField,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route('register.thanks', $application->uuid)
            ->with('success', __('beneficiary_registration.messages.submitted'));
    }

    public function thanks(string $uuid): View
    {
        $application = BeneficiaryApplication::query()
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('pages.beneficiary-registration.thanks', [
            'application' => $application,
        ]);
    }
}
