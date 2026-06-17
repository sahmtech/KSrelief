<?php

namespace App\Jobs;

use App\Models\PatientImportBatch;
use App\Services\PatientImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPatientImportJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public readonly PatientImportBatch $batch
    ) {}

    public function handle(PatientImportService $importService): void
    {
        try {
            $importService->processBatch($this->batch);
        } catch (\Throwable $e) {
            $importService->markBatchFailed($this->batch, $e->getMessage());

            throw $e;
        }
    }
}
