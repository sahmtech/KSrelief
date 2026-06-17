<?php

namespace App\Http\Controllers\Settings;

use App\Enums\SettingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StorePatientStageRequest;
use App\Http\Requests\Settings\UpdatePatientStageRequest;
use App\Models\PatientStage;
use App\Services\Settings\PatientStageSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientStageController extends Controller
{
    public function __construct(
        private readonly PatientStageSettingService $patientStageSettingService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PatientStage::class);

        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status'),
        ];

        return view('pages.settings.patient-stages.index', [
            'patientStages' => $this->patientStageSettingService->paginate(
                $filters['search'],
                $filters['status']
            ),
            'filters' => $filters,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', PatientStage::class);

        return view('pages.settings.patient-stages.create', [
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function store(StorePatientStageRequest $request): RedirectResponse
    {
        $this->patientStageSettingService->create($request->validated());

        return redirect()
            ->route('settings.patient-stages.index')
            ->with('success', __('settings.entities.patient_stages.messages.created'));
    }

    public function show(PatientStage $patientStage): View
    {
        $this->authorize('view', $patientStage);

        $patientStage->load(['creator', 'updater']);

        return view('pages.settings.patient-stages.show', [
            'patientStage' => $patientStage,
        ]);
    }

    public function edit(PatientStage $patientStage): View
    {
        $this->authorize('update', $patientStage);

        return view('pages.settings.patient-stages.edit', [
            'patientStage' => $patientStage,
            'statuses' => SettingStatus::cases(),
        ]);
    }

    public function update(UpdatePatientStageRequest $request, PatientStage $patientStage): RedirectResponse
    {
        $this->patientStageSettingService->update($patientStage, $request->validated());

        return redirect()
            ->route('settings.patient-stages.show', $patientStage)
            ->with('success', __('settings.entities.patient_stages.messages.updated'));
    }

    public function destroy(PatientStage $patientStage): RedirectResponse
    {
        $this->authorize('delete', $patientStage);

        $this->patientStageSettingService->delete($patientStage);

        return redirect()
            ->route('settings.patient-stages.index')
            ->with('success', __('settings.entities.patient_stages.messages.deleted'));
    }
}
