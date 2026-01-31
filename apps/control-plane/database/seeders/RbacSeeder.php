<?php

namespace Database\Seeders;

use App\Support\Rbac\RbacService;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Seed RBAC roles and permissions.
     * Uses RbacService::sync() to ensure consistency.
     */
    public function run(): void
    {
        // Sync permissions and create roles with their permission assignments
        // This will create the 7 roles: saasOwner, saasAdmin, siteOwner, siteAdmin, siteEditor, siteTranslator, siteViewer
        RbacService::sync(assignAllToSuperAdmin: true);
    }
}


