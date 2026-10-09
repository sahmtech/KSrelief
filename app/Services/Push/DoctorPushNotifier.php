<?php

namespace App\Services\Push;

use App\Enums\PassengerType;
use App\Enums\PushNotificationType;
use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientStageHistory;
use App\Models\TransportationTrip;
use App\Models\TransportationTripPassenger;
use App\Models\User;

final class DoctorPushNotifier
{
    public function __construct(
        private readonly PushDispatchService $dispatch,
    ) {}

    public function patientCreated(Patient $patient, User $actor): void
    {
        $patient->loadMissing('campaign');

        $this->dispatch->notifyCampaign(
            (int) $patient->campaign_id,
            PushNotificationType::PatientCreated,
            __('push.titles.patient_created'),
            __('push.bodies.patient_created', [
                'name' => $patient->patient_name,
                'campaign' => $patient->campaign?->name ?? '',
            ]),
            [
                'patient_id' => $patient->id,
                'campaign_id' => $patient->campaign_id,
                'file_number' => $patient->file_number,
            ],
            $actor,
        );
    }

    public function patientStageChanged(Patient $patient, PatientStageHistory $history, User $actor): void
    {
        $history->loadMissing(['toStage', 'fromStage']);
        $patient->loadMissing('campaign');

        $this->dispatch->notifyCampaign(
            (int) $patient->campaign_id,
            PushNotificationType::PatientStageChanged,
            __('push.titles.patient_stage_changed'),
            __('push.bodies.patient_stage_changed', [
                'name' => $patient->patient_name,
                'stage' => $history->toStage?->displayName() ?? '',
            ]),
            [
                'patient_id' => $patient->id,
                'campaign_id' => $patient->campaign_id,
                'stage_id' => $history->to_stage_id,
                'history_id' => $history->id,
            ],
            $actor,
        );
    }

    public function medicalRecordCreated(MedicalRecord $record, User $actor): void
    {
        $record->loadMissing(['patient.campaign', 'stage']);

        $patient = $record->patient;

        if (! $patient) {
            return;
        }

        $this->dispatch->notifyCampaign(
            (int) $patient->campaign_id,
            PushNotificationType::MedicalRecordCreated,
            __('push.titles.medical_record_created'),
            __('push.bodies.medical_record_created', [
                'name' => $patient->patient_name,
                'stage' => $record->stage?->displayName() ?? '',
            ]),
            [
                'patient_id' => $patient->id,
                'record_id' => $record->id,
                'campaign_id' => $patient->campaign_id,
                'stage_id' => $record->stage_id,
            ],
            $actor,
        );
    }

    public function activityCreated(Activity $activity, User $actor): void
    {
        $activity->loadMissing(['campaign', 'activityType']);

        $this->dispatch->notifyCampaign(
            (int) $activity->campaign_id,
            PushNotificationType::ActivityCreated,
            __('push.titles.activity_created'),
            __('push.bodies.activity_created', [
                'title' => $activity->title,
                'type' => $activity->activityType?->name ?? '',
            ]),
            [
                'activity_id' => $activity->id,
                'campaign_id' => $activity->campaign_id,
            ],
            $actor,
        );
    }

    public function activityStatusChanged(Activity $activity, User $actor): void
    {
        $activity->loadMissing('campaign');

        $this->dispatch->notifyCampaign(
            (int) $activity->campaign_id,
            PushNotificationType::ActivityStatusChanged,
            __('push.titles.activity_status_changed'),
            __('push.bodies.activity_status_changed', [
                'title' => $activity->title,
                'status' => $activity->statusLabel(),
            ]),
            [
                'activity_id' => $activity->id,
                'campaign_id' => $activity->campaign_id,
                'status' => $activity->status?->value,
            ],
            $actor,
        );
    }

    public function activityParticipantAdded(Activity $activity, ActivityParticipant $participant, User $actor): void
    {
        $activity->loadMissing('campaign');
        $participant->loadMissing(['patient', 'member']);

        $subject = $participant->participant_type === PassengerType::Patient
            ? ($participant->patient?->patient_name ?? '')
            : ($participant->member?->full_name ?? '');

        $this->dispatch->notifyCampaign(
            (int) $activity->campaign_id,
            PushNotificationType::ActivityParticipantAdded,
            __('push.titles.activity_participant_added'),
            __('push.bodies.activity_participant_added', [
                'activity' => $activity->title,
                'name' => $subject,
            ]),
            [
                'activity_id' => $activity->id,
                'participant_id' => $participant->id,
                'campaign_id' => $activity->campaign_id,
                'patient_id' => $participant->patient_id,
            ],
            $actor,
        );
    }

    public function transportationPassengerAdded(
        TransportationTrip $trip,
        TransportationTripPassenger $passenger,
        User $actor,
    ): void {
        $trip->loadMissing('campaign');
        $passenger->loadMissing(['patient', 'member']);

        if ($passenger->passenger_type !== PassengerType::Patient || ! $passenger->patient_id) {
            return;
        }

        $this->dispatch->notifyCampaign(
            (int) $trip->campaign_id,
            PushNotificationType::TransportationPassengerAdded,
            __('push.titles.transportation_passenger_added'),
            __('push.bodies.transportation_passenger_added', [
                'patient' => $passenger->patient?->patient_name ?? '',
                'trip' => $trip->trip_code ?? (string) $trip->id,
            ]),
            [
                'trip_id' => $trip->id,
                'passenger_id' => $passenger->id,
                'patient_id' => $passenger->patient_id,
                'campaign_id' => $trip->campaign_id,
            ],
            $actor,
        );
    }

    public function transportationStatusChanged(TransportationTrip $trip, User $actor): void
    {
        $trip->loadMissing('campaign');

        $this->dispatch->notifyCampaign(
            (int) $trip->campaign_id,
            PushNotificationType::TransportationStatusChanged,
            __('push.titles.transportation_status_changed'),
            __('push.bodies.transportation_status_changed', [
                'trip' => $trip->trip_code ?? (string) $trip->id,
                'status' => $trip->statusLabel(),
            ]),
            [
                'trip_id' => $trip->id,
                'campaign_id' => $trip->campaign_id,
                'status' => $trip->status?->value,
            ],
            $actor,
        );
    }

    public function patientImportApproved(int $campaignId, string $campaignName, int $patientCount, User $actor): void
    {
        $this->dispatch->notifyCampaign(
            $campaignId,
            PushNotificationType::PatientImportApproved,
            __('push.titles.patient_import_approved'),
            __('push.bodies.patient_import_approved', [
                'count' => $patientCount,
                'campaign' => $campaignName,
            ]),
            [
                'campaign_id' => $campaignId,
                'patients_count' => $patientCount,
            ],
            $actor,
        );
    }
}
