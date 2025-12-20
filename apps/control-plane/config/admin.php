<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Route Prefix
    |--------------------------------------------------------------------------
    |
    | All admin/control-plane routes must live under this URL prefix.
    | Example: /bsadm
    |
    */
    'prefix' => env('ADMIN_PREFIX', 'bsadm'),

    /*
    |--------------------------------------------------------------------------
    | Admin Route Name Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for named routes in the admin area. Example: bsadm.dashboard
    |
    */
    'route_name' => env('ADMIN_ROUTE_NAME', 'bsadm.'),
];


