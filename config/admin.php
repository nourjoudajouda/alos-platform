<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Session (ALOS-S1-37)
    |--------------------------------------------------------------------------
    |
    | Optional admin-specific session settings. When null, uses default
    | SESSION_LIFETIME. Structure for future inactivity timeout enforcement.
    |
    */

    'session' => [
        'lifetime_minutes' => env('ADMIN_SESSION_LIFETIME', null), // null = use SESSION_LIFETIME
    ],

];
