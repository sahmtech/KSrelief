<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Member;
use App\Models\Patient;
use App\Models\PatientStage;
use Illuminate\Support\Collection;

class WorkflowStatisticsService
{
    public function __construct(
        private readonly PatientStatisticsService $patientStatisticsService,
    ) {}

    /**
     * @return array{
     *     patients_by_stage: Collection,
     *     workflow_progress: int,
     *     completed_cases: int,
     *     patients_waiting: int,
     *     total_patients: int,
     *     active_campaigns: int,
     *     medical_staff: int,
     * }
     */
    public function getDashboardStats(): array
    {
        $counts = $this->patientStatisticsService->getPatientCounts();
        $stageStats = $this->patientStatisticsService->getStageStats();
        $total = $counts['total'];
        $completed = $counts['completed'];
        $waiting = max(0, $total - $completed);

        return [
            'patients_by_stage' => $stageStats,
            'workflow_progress' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'completed_cases'   => $completed,
            'patients_waiting'  => $waiting,
            'total_patients'    => $total,
            'active_campaigns'  => Campaign::query()->count(),
            'medical_staff'     => Member::query()->where('status', 'active')->count(),
        ];
    }

    /**
     * @return Collection<int, Patient>
     */
    public function getRecentPatients(int $limit = 5): Collection
    {
        return Patient::query()
            ->with(['campaign', 'currentStage', 'eligibilityStatus'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, array{month: string, count: int}>
     */
    public function getMonthlyRegistrations(int $months = 6): array
    {
        $results = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Patient::query()
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $results[] = [
                'month' => $date->format('M'),
                'count' => $count,
            ];
        }

        return $results;
    }

    public function getCompletedStageId(): ?int
    {
        return PatientStage::query()->where('code', 'completed')->value('id');
    }
}
