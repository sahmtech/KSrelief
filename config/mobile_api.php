<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Campaign-scoped patient visibility (mobile API)
    |--------------------------------------------------------------------------
    |
    | When true, users who are not super_admin / campaign_manager only see
    | patients belonging to campaigns they are assigned to (member or campaign_user).
    |
    */
    'enforce_campaign_scope' => env('MOBILE_API_CAMPAIGN_SCOPE', true),

];
