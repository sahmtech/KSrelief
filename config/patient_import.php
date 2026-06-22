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

];
