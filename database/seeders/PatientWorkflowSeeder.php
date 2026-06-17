<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignMember;
use App\Models\MedicalRecord;
use App\Models\Member;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Models\User;
use App\Services\PatientWorkflowService;
use Illuminate\Database\Seeder;

class PatientWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->first();
        $campaign = Campaign::query()->first();
        $stages = PatientStage::query()->pluck('id', 'code');

        if (! $admin || ! $campaign) {
            return;
        }

        $this->seedCampaignTeam($campaign, $admin);

        $workflowService = app(PatientWorkflowService::class);

        Patient::query()->each(function (Patient $patient) use ($workflowService, $admin): void {
            if ($patient->current_stage_id) {
                $workflowService->recordInitialStage($patient, $admin, __('workflow.messages.initial_stage'));
            }
        });

        $omar = Patient::query()->where('file_number', 'P-2026-002')->first();
        $admissionStageId = $stages['admission'] ?? null;
        $anesthesiaStageId = $stages['anesthesia'] ?? null;

        if ($omar && $admissionStageId && $anesthesiaStageId && $omar->current_stage_id === $anesthesiaStageId) {
            if (! $omar->stageHistories()->where('to_stage_id', $anesthesiaStageId)->exists()) {
                $workflowService->changeStage($omar, $anesthesiaStageId, $admin, __('workflow.messages.demo_stage_transition'));
            }

            if (! $omar->medicalRecords()->exists()) {
                $doctor = Member::query()->whereHas('memberRole', fn ($q) => $q->where('code', 'doctor'))->first();

                MedicalRecord::query()->create([
                    'patient_id' => $omar->id,
                    'stage_id' => $admissionStageId,
                    'record_date' => now()->subDays(2)->toDateString(),
                    'fields_json' => [
                        'admission_notes' => 'Patient admitted for cardiac screening.',
                        'initial_assessment' => 'Stable vitals. Cleared for anesthesia evaluation.',
                        'coordinator' => Member::query()->whereHas('memberRole', fn ($q) => $q->where('code', 'coordinator'))->value('id'),
                    ],
                    'notes' => 'Admission record created during workflow seeding.',
                    'submitted_by' => $admin->id,
                ]);

                MedicalRecord::query()->create([
                    'patient_id' => $omar->id,
                    'stage_id' => $anesthesiaStageId,
                    'record_date' => now()->subDay()->toDateString(),
                    'fields_json' => [
                        'weight' => 32.5,
                        'anesthesia_notes' => 'General anesthesia plan reviewed.',
                        'readiness_status' => 'Ready',
                        'comments' => 'No contraindications identified.',
                        'attending_doctor' => $doctor?->id,
                    ],
                    'notes' => 'Anesthesia assessment completed.',
                    'submitted_by' => $admin->id,
                ]);
            }
        }

        $fatima = Patient::query()->where('file_number', 'P-2026-001')->first();
        if ($fatima && $admissionStageId && ! $fatima->medicalRecords()->exists()) {
            MedicalRecord::query()->create([
                'patient_id' => $fatima->id,
                'stage_id' => $admissionStageId,
                'record_date' => now()->toDateString(),
                'fields_json' => [
                    'admission_notes' => 'Initial admission completed.',
                    'initial_assessment' => 'Awaiting full medical workup.',
                ],
                'submitted_by' => $admin->id,
            ]);
        }
    }

    private function seedCampaignTeam(Campaign $campaign, User $admin): void
    {
        $members = Member::query()->limit(3)->get();

        foreach ($members as $member) {
            CampaignMember::query()->updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'member_id' => $member->id,
                ],
                [
                    'assigned_role' => $member->memberRole?->name,
                    'assigned_from' => now()->subMonth(),
                    'created_by' => $admin->id,
                ]
            );
        }
    }
}
