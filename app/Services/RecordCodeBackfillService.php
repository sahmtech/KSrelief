<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Patient;
use App\Support\RecordCodeGenerator;
use Illuminate\Support\Facades\DB;

class RecordCodeBackfillService
{
    public function __construct(
        private readonly RecordCodeGenerator $codeGenerator,
    ) {}

    /**
     * @return array{campaigns: int, patients: int}
     */
    public function missingCounts(): array
    {
        return [
            'campaigns' => $this->campaignsMissingCodeQuery()->count(),
            'patients' => $this->patientsMissingFileNumberQuery()->count(),
        ];
    }

    /**
     * @return array{campaigns: int, patients: int}
     */
    public function backfill(): array
    {
        $campaignsUpdated = 0;
        $patientsUpdated = 0;

        DB::transaction(function () use (&$campaignsUpdated, &$patientsUpdated): void {
            $this->campaignsMissingCodeQuery()
                ->orderBy('id')
                ->each(function (Campaign $campaign) use (&$campaignsUpdated): void {
                    $campaign->update([
                        'code' => $this->codeGenerator->generateCampaignCode($campaign),
                    ]);
                    $campaignsUpdated++;
                });

            $this->patientsMissingFileNumberQuery()
                ->orderBy('campaign_id')
                ->orderBy('id')
                ->each(function (Patient $patient) use (&$patientsUpdated): void {
                    $campaign = Campaign::query()->lockForUpdate()->findOrFail($patient->campaign_id);

                    $patient->update([
                        'file_number' => $this->codeGenerator->generatePatientFileNumber($campaign, $patient->id),
                    ]);
                    $patientsUpdated++;
                });
        });

        return [
            'campaigns' => $campaignsUpdated,
            'patients' => $patientsUpdated,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Campaign>
     */
    private function campaignsMissingCodeQuery()
    {
        return Campaign::query()
            ->with('country')
            ->where(function ($query): void {
                $query->whereNull('code')->orWhere('code', '');
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Patient>
     */
    private function patientsMissingFileNumberQuery()
    {
        return Patient::query()
            ->with(['campaign.country'])
            ->where(function ($query): void {
                $query->whereNull('file_number')->orWhere('file_number', '');
            });
    }
}
