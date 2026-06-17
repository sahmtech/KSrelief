<?php

namespace App\Services;

use App\Enums\AdmissionStatus;
use App\Models\Patient;
use App\Models\PatientEligibilityStatus;
use App\Models\PatientStage;
use Illuminate\Support\Collection;

class PatientStatisticsService
{
    /**
     * @return array<string, int>
     */
    public function getPatientCounts(?int $campaignId = null): array
    {
        $query = Patient::query()->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId));

        $eligibilityCodes = PatientEligibilityStatus::query()
            ->pluck('id', 'code');

        $completedStageId = PatientStage::query()->where('code', 'completed')->value('id');

        return [
            'total' => (clone $query)->count(),
            'accepted' => (clone $query)->where('eligibility_status_id', $eligibilityCodes['accepted'] ?? 0)->count(),
            'rejected' => (clone $query)->where('eligibility_status_id', $eligibilityCodes['rejected'] ?? 0)->count(),
            'postponed' => (clone $query)->where('eligibility_status_id', $eligibilityCodes['postponed'] ?? 0)->count(),
            'cancelled' => (clone $query)->where('eligibility_status_id', $eligibilityCodes['cancelled'] ?? 0)->count(),
            'admitted' => (clone $query)->where('admission_status', AdmissionStatus::Admitted)->count(),
            'completed' => $completedStageId
                ? (clone $query)->where('current_stage_id', $completedStageId)->count()
                : 0,
        ];
    }

    /**
     * @return Collection<int, array{status: PatientEligibilityStatus, count: int}>
     */
    public function getEligibilityStats(?int $campaignId = null): Collection
    {
        $statuses = PatientEligibilityStatus::query()->active()->ordered()->get();

        return $statuses->map(function (PatientEligibilityStatus $status) use ($campaignId): array {
            $count = Patient::query()
                ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
                ->where('eligibility_status_id', $status->id)
                ->count();

            return ['status' => $status, 'count' => $count];
        });
    }

    /**
     * @return Collection<int, array{stage: PatientStage, count: int}>
     */
    public function getStageStats(?int $campaignId = null): Collection
    {
        $stages = PatientStage::query()->active()->ordered()->get();

        return $stages->map(function (PatientStage $stage) use ($campaignId): array {
            $count = Patient::query()
                ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
                ->where('current_stage_id', $stage->id)
                ->count();

            return ['stage' => $stage, 'count' => $count];
        });
    }
}
