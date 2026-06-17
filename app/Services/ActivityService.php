<?php

namespace App\Services;

use App\Enums\ActivityLogEventType;
use App\Enums\ActivityParticipationStatus;
use App\Enums\ActivityStatus;
use App\Enums\PassengerType;
use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\ActivityStatusLog;
use App\Models\CampaignMember;
use App\Models\Patient;
use App\Models\PatientStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ActivityService
{
    public const AUDIT_CREATED = 'activity.created';

    public const AUDIT_UPDATED = 'activity.updated';

    public const AUDIT_DELETED = 'activity.deleted';

    public const AUDIT_PARTICIPANT_ADDED = 'activity.participant.added';

    public const AUDIT_PARTICIPANT_REMOVED = 'activity.participant.removed';

    public const AUDIT_STATUS_CHANGED = 'activity.status.changed';

  /** Future: sync activity attendance to Attendance module records. */
    public const SYNC_ATTENDANCE_PLACEHOLDER = 'activity.sync.attendance';

    /** Future: link activity to Transportation trips. */
    public const LINK_TRANSPORTATION_PLACEHOLDER = 'activity.link.transportation';

    public function createActivity(array $data, User $user): Activity
    {
        return DB::transaction(function () use ($data, $user): Activity {
            $activity = Activity::create([
                'campaign_id' => $data['campaign_id'],
                'activity_type_id' => $data['activity_type_id'],
                'patient_stage_id' => $data['patient_stage_id'] ?? $this->resolveWorkflowStageId($data['activity_type_id'] ?? null),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'activity_date' => $data['activity_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'location' => $data['location'] ?? null,
                'status' => ActivityStatus::Planned,
                'max_participants' => $data['max_participants'] ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->logEvent(
                $activity,
                ActivityLogEventType::Created,
                null,
                ActivityStatus::Planned,
                $user,
                __('activities.messages.activity_created')
            );

            // Future: dispatch audit event self::AUDIT_CREATED

            return $activity->load(['campaign', 'activityType', 'patientStage', 'creator']);
        });
    }

    public function updateActivity(Activity $activity, array $data, User $user): Activity
    {
        if (! $activity->isEditable()) {
            throw new InvalidArgumentException(__('activities.errors.not_editable'));
        }

        return DB::transaction(function () use ($activity, $data, $user): Activity {
            $activity->update([
                'campaign_id' => $data['campaign_id'] ?? $activity->campaign_id,
                'activity_type_id' => $data['activity_type_id'] ?? $activity->activity_type_id,
                'patient_stage_id' => array_key_exists('patient_stage_id', $data)
                    ? $data['patient_stage_id']
                    : ($activity->patient_stage_id ?? $this->resolveWorkflowStageId($data['activity_type_id'] ?? $activity->activity_type_id)),
                'title' => $data['title'] ?? $activity->title,
                'description' => array_key_exists('description', $data) ? $data['description'] : $activity->description,
                'activity_date' => $data['activity_date'] ?? $activity->activity_date,
                'start_time' => $data['start_time'] ?? $activity->start_time,
                'end_time' => $data['end_time'] ?? $activity->end_time,
                'location' => array_key_exists('location', $data) ? $data['location'] : $activity->location,
                'max_participants' => array_key_exists('max_participants', $data) ? $data['max_participants'] : $activity->max_participants,
                'updated_by' => $user->id,
            ]);

            // Future: dispatch audit event self::AUDIT_UPDATED

            return $activity->fresh(['campaign', 'activityType', 'patientStage', 'creator', 'updater']);
        });
    }

    public function rescheduleActivity(Activity $activity, array $data, User $user): Activity
    {
        if (! $activity->isEditable()) {
            throw new InvalidArgumentException(__('activities.errors.not_editable'));
        }

        return $this->updateActivity($activity, [
            'activity_date' => $data['activity_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ], $user);
    }

    public function deleteActivity(Activity $activity): void
    {
        if ($activity->status === ActivityStatus::InProgress) {
            throw new InvalidArgumentException(__('activities.errors.cannot_delete_in_progress'));
        }

        DB::transaction(function () use ($activity): void {
            // Future: dispatch audit event self::AUDIT_DELETED
            $activity->delete();
        });
    }

    public function addParticipant(Activity $activity, array $data, User $user): ActivityParticipant
    {
        if (! $activity->isEditable()) {
            throw new InvalidArgumentException(__('activities.errors.not_editable'));
        }

        $participantType = PassengerType::from($data['participant_type']);

        return DB::transaction(function () use ($activity, $data, $participantType, $user): ActivityParticipant {
            if ($participantType === PassengerType::Member) {
                $memberId = (int) $data['member_id'];
                $this->assertMemberAssignedToCampaign($activity->campaign_id, $memberId);
                $this->assertParticipantNotRegistered($activity, PassengerType::Member, $memberId);
            } else {
                $patientId = (int) $data['patient_id'];
                $this->assertPatientBelongsToCampaign($activity->campaign_id, $patientId);
                $this->assertParticipantNotRegistered($activity, PassengerType::Patient, $patientId);
            }

            $this->assertCapacityAvailable($activity);

            $participant = ActivityParticipant::create([
                'activity_id' => $activity->id,
                'participant_type' => $participantType,
                'member_id' => $participantType === PassengerType::Member ? $data['member_id'] : null,
                'patient_id' => $participantType === PassengerType::Patient ? $data['patient_id'] : null,
                'attendance_status' => $data['attendance_status'] ?? ActivityParticipationStatus::Registered,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->logEvent(
                $activity,
                ActivityLogEventType::ParticipantAdded,
                null,
                null,
                $user,
                $participant->participantName()
            );

            // Future: self::SYNC_ATTENDANCE_PLACEHOLDER
            // Future: dispatch audit event self::AUDIT_PARTICIPANT_ADDED

            return $participant->load(['member.memberRole', 'patient']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{added: int, skipped: int}
     */
    public function bulkAddParticipants(Activity $activity, array $rows, User $user): array
    {
        $added = 0;
        $skipped = 0;

        DB::transaction(function () use ($activity, $rows, $user, &$added, &$skipped): void {
            foreach ($rows as $row) {
                if (empty($row['participant_type'])) {
                    $skipped++;

                    continue;
                }

                try {
                    $this->addParticipant($activity, $row, $user);
                    $added++;
                } catch (InvalidArgumentException) {
                    $skipped++;
                }
            }
        });

        return ['added' => $added, 'skipped' => $skipped];
    }

    public function removeParticipant(ActivityParticipant $participant, User $user): void
    {
        $activity = $participant->activity;

        if (! $activity->isEditable()) {
            throw new InvalidArgumentException(__('activities.errors.not_editable'));
        }

        DB::transaction(function () use ($participant, $activity, $user): void {
            $name = $participant->participantName();

            $this->logEvent(
                $activity,
                ActivityLogEventType::ParticipantRemoved,
                null,
                null,
                $user,
                $name
            );

            // Future: dispatch audit event self::AUDIT_PARTICIPANT_REMOVED
            $participant->delete();
        });
    }

    public function changeStatus(
        Activity $activity,
        ActivityStatus $newStatus,
        User $user,
        ?string $notes = null
    ): Activity {
        $current = $activity->status;

        if (! $current->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(__('activities.errors.invalid_status_transition'));
        }

        return DB::transaction(function () use ($activity, $current, $newStatus, $user, $notes): Activity {
            $activity->update([
                'status' => $newStatus,
                'updated_by' => $user->id,
            ]);

            $this->logEvent(
                $activity,
                ActivityLogEventType::StatusChange,
                $current,
                $newStatus,
                $user,
                $notes
            );

            // Future: self::SYNC_ATTENDANCE_PLACEHOLDER on complete
            // Future: dispatch audit event self::AUDIT_STATUS_CHANGED

            return $activity->fresh(['campaign', 'activityType', 'statusLogs.changedBy']);
        });
    }

    public function assertMemberAssignedToCampaign(int $campaignId, int $memberId): void
    {
        $assigned = CampaignMember::query()
            ->where('campaign_id', $campaignId)
            ->where('member_id', $memberId)
            ->where(function ($query): void {
                $query->whereNull('assigned_to')
                    ->orWhereDate('assigned_to', '>=', now());
            })
            ->exists();

        if (! $assigned) {
            throw new InvalidArgumentException(__('activities.errors.member_not_assigned'));
        }
    }

    public function assertPatientBelongsToCampaign(int $campaignId, int $patientId): void
    {
        $belongs = Patient::query()
            ->where('id', $patientId)
            ->where('campaign_id', $campaignId)
            ->exists();

        if (! $belongs) {
            throw new InvalidArgumentException(__('activities.errors.patient_not_in_campaign'));
        }
    }

    private function assertParticipantNotRegistered(Activity $activity, PassengerType $type, int $id): void
    {
        $exists = ActivityParticipant::query()
            ->where('activity_id', $activity->id)
            ->when($type === PassengerType::Member, fn ($q) => $q->where('member_id', $id))
            ->when($type === PassengerType::Patient, fn ($q) => $q->where('patient_id', $id))
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException(__('activities.errors.participant_already_registered'));
        }
    }

    private function assertCapacityAvailable(Activity $activity): void
    {
        if ($activity->max_participants && $activity->participants()->count() >= $activity->max_participants) {
            throw new InvalidArgumentException(__('activities.errors.capacity_reached'));
        }
    }

    private function resolveWorkflowStageId(?int $activityTypeId): ?int
    {
        if (! $activityTypeId) {
            return null;
        }

        $type = \App\Models\ActivityType::query()->find($activityTypeId);

        if (! $type) {
            return null;
        }

        $stageCode = match ($type->code) {
            'activation' => 'activation',
            'rehab' => 'rehab_education',
            'education' => 'rehab_education',
            default => null,
        };

        if (! $stageCode) {
            return null;
        }

        return PatientStage::query()->where('code', $stageCode)->value('id');
    }

    private function logEvent(
        Activity $activity,
        ActivityLogEventType $eventType,
        ?ActivityStatus $oldStatus,
        ?ActivityStatus $newStatus,
        User $user,
        ?string $notes = null
    ): void {
        ActivityStatusLog::create([
            'activity_id' => $activity->id,
            'event_type' => $eventType,
            'old_status' => $oldStatus?->value,
            'new_status' => $newStatus?->value,
            'changed_by' => $user->id,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }
}
