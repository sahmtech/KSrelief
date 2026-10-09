<?php

return [
    'messages' => [
        'token_registered' => 'Device registered for push notifications.',
        'token_removed' => 'Device unregistered from push notifications.',
        'broadcast_queued' => 'Notification dispatch recorded and delivery started.',
    ],

    'errors' => [
        'no_recipients_selected' => 'Select at least one recipient target.',
    ],

    'titles' => [
        'patient_created' => 'New patient',
        'patient_stage_changed' => 'Workflow update',
        'medical_record_created' => 'New medical record',
        'activity_created' => 'New activity',
        'activity_status_changed' => 'Activity update',
        'activity_participant_added' => 'Activity participant',
        'transportation_passenger_added' => 'Transportation',
        'transportation_status_changed' => 'Trip update',
        'patient_import_approved' => 'Patients imported',
    ],

    'bodies' => [
        'patient_created' => ':name was registered (:campaign).',
        'patient_stage_changed' => ':name moved to :stage.',
        'medical_record_created' => 'Record added for :name (:stage).',
        'activity_created' => ':title (:type).',
        'activity_status_changed' => ':title — :status.',
        'activity_participant_added' => ':name joined :activity.',
        'transportation_passenger_added' => ':patient added to trip :trip.',
        'transportation_status_changed' => 'Trip :trip — :status.',
        'patient_import_approved' => ':count patients imported to :campaign.',
    ],
];
