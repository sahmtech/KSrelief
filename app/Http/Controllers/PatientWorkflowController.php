<?php

namespace App\Http\Controllers;

use App\Http\Requests\Workflow\ChangePatientStageRequest;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Services\PatientWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PatientWorkflowController extends Controller
{
    public function __construct(private readonly PatientWorkflowService $workflowService) {}

    public function timeline(Patient $patient): View
    {
        $this->authorize('viewWorkflow', $patient);

        $patient->load(['currentStage', 'campaign']);

        return view('pages.patients.workflow.timeline', [
            'patient'  => $patient,
            'timeline' => $this->workflowService->getTimeline($patient),
            'stages'   => PatientStage::query()->active()->ordered()->get(),
        ]);
    }

    public function changeStage(ChangePatientStageRequest $request, Patient $patient): RedirectResponse|JsonResponse
    {
        $this->authorize('changeStage', $patient);

        try {
            $this->workflowService->changeStage(
                patient: $patient,
                toStageId: (int) $request->validated('to_stage_id'),
                user: $request->user(),
                notes: $request->validated('notes'),
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['to_stage_id' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            $toStage = PatientStage::find($request->validated('to_stage_id'));

            return response()->json([
                'message' => __('workflow.messages.stage_changed'),
                'stage'   => $toStage?->name,
            ]);
        }

        return redirect()
            ->to(route('patients.show', $patient).'#workflow')
            ->with('success', __('workflow.messages.stage_changed'));
    }

    public function history(Patient $patient): View
    {
        $this->authorize('viewStageHistory', $patient);

        $patient->load(['currentStage', 'campaign']);

        return view('pages.patients.workflow.history', [
            'patient' => $patient,
            'history' => $this->workflowService->getHistory($patient),
        ]);
    }
}
