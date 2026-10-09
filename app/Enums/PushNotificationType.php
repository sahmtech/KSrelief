<?php

namespace App\Enums;

enum PushNotificationType: string
{
    case PatientCreated = 'patient_created';
    case PatientStageChanged = 'patient_stage_changed';
    case MedicalRecordCreated = 'medical_record_created';
    case MedicalRecordUpdated = 'medical_record_updated';
    case ActivityCreated = 'activity_created';
    case ActivityStatusChanged = 'activity_status_changed';
    case ActivityParticipantAdded = 'activity_participant_added';
    case TransportationPassengerAdded = 'transportation_passenger_added';
    case TransportationStatusChanged = 'transportation_status_changed';
    case PatientImportApproved = 'patient_import_approved';
    case AdminBroadcast = 'admin_broadcast';

    public function configKey(): string
    {
        return $this->value;
    }
}
