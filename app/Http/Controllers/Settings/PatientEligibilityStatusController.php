<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StorePatientEligibilityStatusRequest;
use App\Http\Requests\Settings\UpdatePatientEligibilityStatusRequest;
use App\Models\PatientEligibilityStatus;
use App\Services\Settings\PatientEligibilityStatusSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientEligibilityStatusController extends Controller
{
    public function __construct(
        private readonly PatientEligibilityStatusSettingService $patientEligibilityStatusSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PatientEligibilityStatus::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.patient-eligibility-statuses.index', [
            'patientEligibilityStatuses' => $this->patientEligibilityStatusSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', PatientEligibilityStatus::class);

        return view('pages.settings.patient-eligibility-statuses.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StorePatientEligibilityStatusRequest $request): RedirectResponse
    {
        $this->patientEligibilityStatusSettingService->create($request->validated());

        return redirect()
            ->route('settings.patient-eligibility-statuses.index')
            ->with('success', __('settings.entities.patient_eligibility_statuses.messages.created'));
    }

    public function show(PatientEligibilityStatus $patientEligibilityStatus): View
    {
        $this->authorize('view', $patientEligibilityStatus);

        $patientEligibilityStatus->load(['creator', 'updater']);

        return view('pages.settings.patient-eligibility-statuses.show', [
            'patientEligibilityStatus' => $patientEligibilityStatus,
        ]);
    }

    public function edit(PatientEligibilityStatus $patientEligibilityStatus): View
    {
        $this->authorize('update', $patientEligibilityStatus);

        return view('pages.settings.patient-eligibility-statuses.edit', [
            'patientEligibilityStatus' => $patientEligibilityStatus,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdatePatientEligibilityStatusRequest $request, PatientEligibilityStatus $patientEligibilityStatus): RedirectResponse
    {
        $this->patientEligibilityStatusSettingService->update($patientEligibilityStatus, $request->validated());

        return redirect()
            ->route('settings.patient-eligibility-statuses.show', $patientEligibilityStatus)
            ->with('success', __('settings.entities.patient_eligibility_statuses.messages.updated'));
    }

    public function destroy(PatientEligibilityStatus $patientEligibilityStatus): RedirectResponse
    {
        $this->authorize('delete', $patientEligibilityStatus);

        $this->patientEligibilityStatusSettingService->delete($patientEligibilityStatus);

        return redirect()
            ->route('settings.patient-eligibility-statuses.index')
            ->with('success', __('settings.entities.patient_eligibility_statuses.messages.deleted'));
    }
}
