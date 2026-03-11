<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ALOS Tenant Defaults (SaaS Registration)
    |--------------------------------------------------------------------------
    | Default limits for new tenants created via public registration.
    */
    'tenant_defaults' => [
        'user_limit' => (int) env('ALOS_TENANT_USER_LIMIT', 10),
        'lawyer_limit' => (int) env('ALOS_TENANT_LAWYER_LIMIT', 5),
        'storage_limit' => (int) env('ALOS_TENANT_STORAGE_LIMIT_MB', 1024),
    ],
];
