<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission Models
    |--------------------------------------------------------------------------
    |
    | These model classes are used to interact with the permissions/roles.
    |
    */
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Tables
    |--------------------------------------------------------------------------
    */
    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Teams / Tenancy
    |--------------------------------------------------------------------------
    |
    | Keep disabled for now (single-tenant baseline).
    | When enabling multi-tenant later, set 'teams' => true and add the
    | team_foreign_key column to related tables (see docs/rbac.md).
    |
    */
    'teams' => false,
    'team_foreign_key' => 'team_id',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guard
    |--------------------------------------------------------------------------
    */
    'guard_name' => 'web',
];


