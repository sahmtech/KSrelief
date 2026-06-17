<?php

namespace Database\Seeders;

use App\Enums\ActivityParticipationStatus;
use App\Enums\ActivityStatus;
use App\Enums\PassengerType;
use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\ActivityType;
use App\Models\Campaign;
use App\Models\Member;
use App\Models\Patient;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Database\Seeder;

class ActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->first();
        $campaign = Campaign::query()->first();

        if (! $admin || ! $campaign) {
            return;
        }

        $types = ActivityType::query()->where('status', 'active')->get()->keyBy('code');
        $member = Member::query()
            ->whereHas('campaignAssignments', fn ($q) => $q->where('campaign_id', $campaign->id))
            ->first();
        $patient = Patient::query()->where('campaign_id', $campaign->id)->first();

        if ($types->isEmpty()) {
            return;
        }

        /** @var ActivityService $activityService */
        $activityService = app(ActivityService::class);

        $activities = [
            [
                'code' => 'activation',
                'title' => 'Morning Activation Session',
                'description' => 'Group activation therapy for post-operative patients.',
                'date' => now()->toDateString(),
                'start' => '09:00',
                'end' => '10:30',
                'location' => 'Rehabilitation Hall A',
                'status' => ActivityStatus::Planned,
            ],
            [
                'code' => 'education',
                'title' => 'Patient Education Workshop',
                'description' => 'Hygiene and wound care education.',
                'date' => now()->addDay()->toDateString(),
                'start' => '11:00',
                'end' => '12:00',
                'location' => 'Education Room 2',
                'status' => ActivityStatus::Planned,
            ],
            [
                'code' => 'team_meeting',
                'title' => 'Daily Medical Team Briefing',
                'description' => 'Coordination meeting for surgical and nursing staff.',
                'date' => now()->toDateString(),
                'start' => '07:30',
                'end' => '08:00',
                'location' => 'Campaign HQ',
                'status' => ActivityStatus::Completed,
            ],
            [
                'code' => 'rehab',
                'title' => 'Rehabilitation Follow-up',
                'description' => 'Individual rehabilitation assessments.',
                'date' => now()->subDay()->toDateString(),
                'start' => '14:00',
                'end' => '16:00',
                'location' => 'Physiotherapy Unit',
                'status' => ActivityStatus::Completed,
            ],
        ];

        foreach ($activities as $data) {
            $type = $types->get($data['code']) ?? $types->first();

            $activity = Activity::query()->updateOrCreate(
                [
                    'campaign_id' => $campaign->id,
                    'title' => $data['title'],
                    'activity_date' => $data['date'],
                ],
                [
                    'activity_type_id' => $type->id,
                    'description' => $data['description'],
                    'start_time' => $data['start'],
                    'end_time' => $data['end'],
                    'location' => $data['location'],
                    'status' => $data['status'],
                    'max_participants' => 25,
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );

            if ($activity->wasRecentlyCreated && $data['status'] !== ActivityStatus::Planned) {
                $activity->update(['status' => ActivityStatus::Planned]);
                $activity = $activity->fresh();

                if ($data['status'] === ActivityStatus::Completed) {
                    $activityService->changeStatus($activity, ActivityStatus::InProgress, $admin);
                    $activityService->changeStatus($activity->fresh(), ActivityStatus::Completed, $admin);
                } elseif ($data['status'] === ActivityStatus::Cancelled) {
                    $activityService->changeStatus($activity, ActivityStatus::Cancelled, $admin);
                }
            }

            if ($member && $data['code'] === 'team_meeting') {
                ActivityParticipant::query()->updateOrCreate(
                    [
                        'activity_id' => $activity->id,
                        'member_id' => $member->id,
                    ],
                    [
                        'participant_type' => PassengerType::Member,
                        'attendance_status' => ActivityParticipationStatus::Attended,
                    ]
                );
            }

            if ($patient && in_array($data['code'], ['activation', 'rehab', 'education'], true)) {
                ActivityParticipant::query()->updateOrCreate(
                    [
                        'activity_id' => $activity->id,
                        'patient_id' => $patient->id,
                    ],
                    [
                        'participant_type' => PassengerType::Patient,
                        'attendance_status' => $data['status'] === ActivityStatus::Completed
                            ? ActivityParticipationStatus::Attended
                            : ActivityParticipationStatus::Registered,
                    ]
                );
            }
        }
    }
}
