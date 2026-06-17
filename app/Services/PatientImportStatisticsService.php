<?php

namespace App\Services;

use App\Enums\PatientImportBatchStatus;
use App\Models\PatientImportBatch;

class PatientImportStatisticsService
{
    /**
     * @return array<string, int>
     */
    public function getStats(): array
    {
        return [
            'total' => PatientImportBatch::query()->count(),
            'pending_review' => PatientImportBatch::query()
                ->where('status', PatientImportBatchStatus::Review)
                ->count(),
            'processing' => PatientImportBatch::query()
                ->where('status', PatientImportBatchStatus::Processing)
                ->count(),
            'completed' => PatientImportBatch::query()
                ->where('status', PatientImportBatchStatus::Completed)
                ->count(),
            'failed' => PatientImportBatch::query()
                ->where('status', PatientImportBatchStatus::Failed)
                ->count(),
            'patients_imported' => PatientImportBatch::query()
                ->where('status', PatientImportBatchStatus::Completed)
                ->sum('imported_count'),
        ];
    }
}
