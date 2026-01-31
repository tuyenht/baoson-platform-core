<?php

use App\Enums\Permission;

return [
    /*
     * Permission naming convention:
     *   <domain>.<resource>.<action>
     * Examples:
     *   admin.users.view
     *   admin.users.create
     *   admin.menu.users
     */
    'naming' => [
        'separator' => '.',
    ],

    'roles' => [
        // Platform-level roles
        'saasOwner' => 'saasOwner',
        'saasAdmin' => 'saasAdmin',
        
        // Site-level roles
        'siteOwner' => 'siteOwner',
        'siteAdmin' => 'siteAdmin',
        'siteEditor' => 'siteEditor',
        'siteTranslator' => 'siteTranslator',
        'siteViewer' => 'siteViewer',
    ],

    /*
     * Canonical permissions list (DB-driven).
     * This should be synced via seeder or `php artisan rbac:sync`.
     */
    'permissions' => Permission::class,
];


