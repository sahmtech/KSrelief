<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Push notifications (FCM HTTP v1)
    |--------------------------------------------------------------------------
    |
    | Place the Firebase Admin SDK JSON on the server (never commit it).
    | Default path: storage/app/firebase/credentials.json
    |
    */

    'enabled' => env('PUSH_NOTIFICATIONS_ENABLED', true),

    'credentials_path' => env(
        'FIREBASE_CREDENTIALS',
        storage_path('app/firebase/credentials.json')
    ),

    /**
     * System roles (Spatie) that receive clinical / doctor mobile pushes
     * when assigned to the patient's campaign.
     *
     * @var list<string>
     */
    'recipient_roles' => array_filter(array_map(
        'trim',
        explode(',', env('PUSH_RECIPIENT_ROLES', 'doctor,campaign_coordinator'))
    )),

    /**
     * Toggle automatic event pushes (admin broadcast always respects enabled).
     *
     * @var array<string, bool>
     */
    'events' => [
        'patient_created' => true,
        'patient_stage_changed' => true,
        'medical_record_created' => true,
        'medical_record_updated' => false,
        'activity_created' => true,
        'activity_status_changed' => true,
        'activity_participant_added' => true,
        'transportation_passenger_added' => true,
        'transportation_status_changed' => true,
        'patient_import_approved' => true,
    ],

    'queue' => env('PUSH_NOTIFICATIONS_QUEUE', true),

];
