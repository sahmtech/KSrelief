<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Patient file number format
    |--------------------------------------------------------------------------
    |
    | {COUNTRY}{YYYY}{MM}{SEQUENCE}
    | Example: SYR2026071 = Syria, July 2026, patient #1 in that bucket.
    |
    | Year and month come from the patient's registration date (created_at).
    | Sequence is unique per country/year/month bucket, system-wide.
    |
    */
    'country_prefix_length' => 3,

    'pattern' => '/^[A-Z]{3}\d{6}\d+$/',

];
