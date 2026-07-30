<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Patient;
use App\Support\RecordCodeGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecordCodeBackfillService
{
    public function __construct(
        private readonly RecordCodeGenerator $codeGenerator,
    ) {}

    /**
     * @return array{
     *     campaigns: int,
     *     patients: int,
     *     patients_missing: int,
     *     patients_legacy: int,
     *     patients_modern: int,
     *     legacy_samples: list<array{id: int, patient_name: string, file_number: string|null, created_at: string|null, campaign: string|null}>,
     * }
     */
    public function audit(): array
    {
        $missingCounts = $this->missingCounts();

        $legacyPatients = Patient::query()
            ->whereNotNull('file_number')
            ->where('file_number', '!=', '')
            ->pluck('file_number')
            ->filter(fn (?string $fileNumber): bool => ! $this->codeGenerator->isModernPatientFileNumber($fileNumber))
            ->count();
        $modernPatients = Patient::query()
            ->whereNotNull('file_number')
            ->where('file_number', '!=', '')
            ->get(['id', 'file_number'])
            ->filter(fn (Patient $patient): bool => $this->codeGenerator->isModernPatientFileNumber($patient->file_number))
            ->count();

        return [
            'campaigns' => $missingCounts['campaigns'],
            'patients' => $missingCounts['patients'],
            'patients_missing' => $missingCounts['patients'],
            'patients_legacy' => $legacyPatients,
            'patients_modern' => $modernPatients,
            'legacy_samples' => $this->legacySamples(),
        ];
    }

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
    public function backfillMissing(): array
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
                ->orderBy('created_at')
                ->orderBy('id')
                ->each(function (Patient $patient) use (&$patientsUpdated): void {
                    $campaign = Campaign::query()->lockForUpdate()->findOrFail($patient->campaign_id);

                    $patient->update([
                        'file_number' => $this->codeGenerator->generatePatientFileNumber(
                            $campaign,
                            $patient->id,
                            $patient->created_at ?? now()
                        ),
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
     * @return array{patients: int}
     */
    public function migrateLegacyPatientFileNumbers(): array
    {
        $patientsUpdated = 0;

        DB::transaction(function () use (&$patientsUpdated): void {
            $this->legacyPatientsForMigration()
                ->each(function (Patient $patient) use (&$patientsUpdated): void {
                    $campaign = Campaign::query()->lockForUpdate()->findOrFail($patient->campaign_id);

                    $patient->update([
                        'file_number' => $this->codeGenerator->generatePatientFileNumber(
                            $campaign,
                            $patient->id,
                            $patient->created_at ?? now()
                        ),
                    ]);
                    $patientsUpdated++;
                });
        });

        return [
            'patients' => $patientsUpdated,
        ];
    }

    /**
     * @return array{campaigns: int, patients: int}
     */
    public function backfill(): array
    {
        return $this->backfillMissing();
    }

    /**
     * @return list<array{id: int, patient_name: string, file_number: string|null, created_at: string|null, campaign: string|null}>
     */
    private function legacySamples(): array
    {
        return $this->legacyPatientsForMigration()
            ->take(8)
            ->map(fn (Patient $patient): array => [
                'id' => $patient->id,
                'patient_name' => $patient->patient_name,
                'file_number' => $patient->file_number,
                'created_at' => $patient->created_at?->format('Y-m-d H:i'),
                'campaign' => $patient->campaign?->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Patient>
     */
    private function legacyPatientsForMigration(): Collection
    {
        return Patient::query()
            ->with(['campaign.country'])
            ->whereNotNull('file_number')
            ->where('file_number', '!=', '')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (Patient $patient): bool => ! $this->codeGenerator->isModernPatientFileNumber($patient->file_number))
            ->values();
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
