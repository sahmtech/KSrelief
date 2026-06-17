<?php

namespace Database\Seeders;

use App\Enums\AdmissionStatus;
use App\Enums\Gender;
use App\Enums\PatientRecordStatus;
use App\Models\Campaign;
use App\Models\Patient;
use App\Models\PatientEligibilityStatus;
use App\Models\PatientStage;
use App\Models\User;
use App\Services\PatientService;
use Illuminate\Database\Seeder;

class PatientsSeeder extends Seeder
{
    public function run(): void
    {
        $campaign = Campaign::query()->first();
        $admin = User::query()->where('email', 'admin@example.com')->first();
        $eligibility = PatientEligibilityStatus::query()->pluck('id', 'code');
        $stages = PatientStage::query()->pluck('id', 'code');
        $patientService = app(PatientService::class);

        if (! $campaign || ! $admin) {
            return;
        }

        $samples = [
            [
                'campaign_id' => $campaign->id,
                'patient_name' => 'Fatima Al-Rashid',
                'file_number' => 'P-2026-001',
                'date_of_birth' => '2018-03-15',
                'gender' => Gender::Female->value,
                'contact_number' => '+966501100001',
                'eligibility_status_id' => $eligibility['accepted'] ?? $eligibility->first(),
                'current_stage_id' => $stages['admission'] ?? $stages->first(),
                'admission_status' => AdmissionStatus::Admitted->value,
                'notes' => 'Pediatric cardiac screening case.',
            ],
            [
                'campaign_id' => $campaign->id,
                'patient_name' => 'Omar Al-Shehri',
                'file_number' => 'P-2026-002',
                'date_of_birth' => '2015-07-22',
                'gender' => Gender::Male->value,
                'contact_number' => '+966501100002',
                'eligibility_status_id' => $eligibility['accepted'] ?? $eligibility->first(),
                'current_stage_id' => $stages['anesthesia'] ?? $stages->first(),
                'admission_status' => AdmissionStatus::Admitted->value,
            ],
            [
                'campaign_id' => $campaign->id,
                'patient_name' => 'Layla Al-Qahtani',
                'file_number' => 'P-2026-003',
                'date_of_birth' => '2020-11-08',
                'gender' => Gender::Female->value,
                'contact_number' => '+966501100003',
                'eligibility_status_id' => $eligibility['postponed'] ?? $eligibility->first(),
                'current_stage_id' => $stages['admission'] ?? $stages->first(),
                'admission_status' => AdmissionStatus::NotAdmitted->value,
            ],
            [
                'campaign_id' => $campaign->id,
                'patient_name' => 'Youssef Al-Dosari',
                'file_number' => 'P-2026-004',
                'date_of_birth' => '2012-01-30',
                'gender' => Gender::Male->value,
                'eligibility_status_id' => $eligibility['rejected'] ?? $eligibility->first(),
                'admission_status' => AdmissionStatus::NotAdmitted->value,
            ],
            [
                'campaign_id' => $campaign->id,
                'patient_name' => 'Mariam Al-Harbi',
                'file_number' => 'P-2026-005',
                'date_of_birth' => '2019-05-12',
                'gender' => Gender::Female->value,
                'contact_number' => '+966501100005',
                'eligibility_status_id' => $eligibility['cancelled'] ?? $eligibility->first(),
                'admission_status' => AdmissionStatus::NotAdmitted->value,
            ],
        ];

        foreach ($samples as $sample) {
            $ages = $patientService->calculateAge($sample['date_of_birth']);

            Patient::query()->updateOrCreate(
                [
                    'campaign_id' => $sample['campaign_id'],
                    'file_number' => $sample['file_number'],
                ],
                [
                    ...$sample,
                    'age_years' => $ages['years'],
                    'age_months' => $ages['months'],
                    'status' => PatientRecordStatus::Active->value,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }
    }
}
