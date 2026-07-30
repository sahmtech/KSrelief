<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Synchronous processing
    |--------------------------------------------------------------------------
    |
    | When true, patient import files are processed immediately during upload
    | instead of being queued. Recommended for servers without a queue worker.
    |
    */
    'sync_processing' => env('PATIENT_IMPORT_SYNC', true),

    /*
    |--------------------------------------------------------------------------
    | Basic import columns (campaign is selected in the UI, not in the file)
    |--------------------------------------------------------------------------
    */
    'required_columns' => [
        'patient_name',
        'date_of_birth',
        'gender',
        'eligibility_status',
        'admission_status',
    ],

    'optional_columns' => [
        'file_number',
        'height_cm',
        'weight_kg',
        'contact_number',
        'stage',
        'surgery_day_number',
        'rank',
        'surgical_side',
        'approval_reason',
        'patient_notes',
    ],

];
