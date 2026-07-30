<?php

namespace App\Support;

use App\Models\ImplantCompany;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientStage;
use Illuminate\Support\Collection;

final class PatientFileNumberStyleSupport
{
    /**
     * @param  Collection<int, Patient>|list<Patient>  $patients
     */
    public static function applyAccentColors(Collection|array $patients): void
    {
        $patients = $patients instanceof Collection ? $patients : collect($patients);

        if ($patients->isEmpty()) {
            return;
        }

        $operationStageId = PatientStage::query()
            ->where('code', 'operation')
            ->value('id');

        if (! $operationStageId) {
            return;
        }

        $records = MedicalRecord::query()
            ->whereIn('patient_id', $patients->pluck('id'))
            ->where('stage_id', $operationStageId)
            ->orderByDesc('record_date')
            ->orderByDesc('id')
            ->get()
            ->unique('patient_id');

        $companyIds = $records
            ->map(fn (MedicalRecord $record): mixed => $record->field('implant_company_id'))
            ->filter(fn (mixed $value): bool => filled($value))
            ->map(fn (mixed $value): int => (int) $value)
            ->unique()
            ->values();

        $companyColors = $companyIds->isEmpty()
            ? collect()
            : ImplantCompany::query()->whereIn('id', $companyIds)->pluck('color', 'id');

        $colorByPatient = [];

        foreach ($records as $record) {
            $companyId = $record->field('implant_company_id');

            if (! filled($companyId)) {
                continue;
            }

            $color = $companyColors->get((int) $companyId);

            if (filled($color)) {
                $colorByPatient[$record->patient_id] = $color;
            }
        }

        foreach ($patients as $patient) {
            $patient->setAttribute('file_number_accent_color', $colorByPatient[$patient->id] ?? null);
        }
    }
}
