<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientStage;
use App\Models\PatientStageHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PatientWorkflowService
{
    public const AUDIT_STAGE_CHANGED = 'workflow.stage.changed';

    /**
     * Record the initial stage entry when a patient is first assigned to a stage.
     */
    public function recordInitialStage(Patient $patient, User $user, ?string $notes = null): ?PatientStageHistory
    {
        if (! $patient->current_stage_id) {
            return null;
        }

        if ($patient->stageHistories()->where('to_stage_id', $patient->current_stage_id)->exists()) {
            return null;
        }

        return PatientStageHistory::create([
            'patient_id'    => $patient->id,
            'from_stage_id' => null,
            'to_stage_id'   => $patient->current_stage_id,
            'changed_by'    => $user->id,
            'changed_at'    => now(),
            'notes'         => $notes,
        ]);
    }

    /**
     * Change the current stage of a patient.
     * Creates a stage history record and updates the patient's current stage within a transaction.
     */
    public function changeStage(Patient $patient, int $toStageId, User $user, ?string $notes = null): PatientStageHistory
    {
        if ($patient->current_stage_id === $toStageId) {
            throw new \InvalidArgumentException(__('workflow.errors.same_stage'));
        }

        return DB::transaction(function () use ($patient, $toStageId, $user, $notes): PatientStageHistory {
            $history = PatientStageHistory::create([
                'patient_id'   => $patient->id,
                'from_stage_id' => $patient->current_stage_id,
                'to_stage_id'  => $toStageId,
                'changed_by'   => $user->id,
                'changed_at'   => now(),
                'notes'        => $notes,
            ]);

            $patient->update([
                'current_stage_id' => $toStageId,
                'updated_by'       => $user->id,
            ]);

            // Future: dispatch(new AuditEvent(self::AUDIT_STAGE_CHANGED, $patient, $user));

            return $history->load(['fromStage', 'toStage', 'changedBy']);
        });
    }

    /**
     * Get the complete ordered stage history for a patient.
     *
     * @return Collection<int, PatientStageHistory>
     */
    public function getHistory(Patient $patient): Collection
    {
        return $patient->stageHistories()
            ->with(['fromStage', 'toStage', 'changedBy'])
            ->orderBy('changed_at', 'desc')
            ->get();
    }

    /**
     * Get the timeline: all active stages with completion status based on history.
     *
     * @return array<int, array{stage: PatientStage, completed: bool, history: PatientStageHistory|null}>
     */
    public function getTimeline(Patient $patient): array
    {
        $stages = PatientStage::query()
            ->active()
            ->ordered()
            ->get();

        $historyByStage = $patient->stageHistories()
            ->with(['changedBy'])
            ->orderBy('changed_at')
            ->get()
            ->keyBy('to_stage_id');

        $currentStageId = $patient->current_stage_id;

        return $stages->map(function (PatientStage $stage) use ($historyByStage, $currentStageId): array {
            $history = $historyByStage->get($stage->id);
            $isCurrent = $stage->id === $currentStageId;
            $completed = $history !== null && ! $isCurrent;

            return [
                'stage'     => $stage,
                'completed' => $completed,
                'current'   => $isCurrent,
                'pending'   => ! $completed && ! $isCurrent,
                'history'   => $history,
            ];
        })->all();
    }

    /**
     * Get the current stage of a patient (with eager-loaded model).
     */
    public function getCurrentStage(Patient $patient): ?PatientStage
    {
        return $patient->currentStage ?? PatientStage::query()->active()->default()->first();
    }
}
