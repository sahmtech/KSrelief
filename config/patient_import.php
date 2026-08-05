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
    | Official import template (single sheet, fixed column order)
    |--------------------------------------------------------------------------
    */
    'template_columns' => [
        'patient_name',
        'date_of_birth',
        'gender',
        'height_cm',
        'weight_kg',
        'contact_number',
    ],

    'required_columns' => [
        'patient_name',
    ],

    'default_date_of_birth' => '2000-01-01',

    'default_gender' => 'male',

];