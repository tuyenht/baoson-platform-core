<?php

namespace Database\Seeders;

use App\Enums\Permission as P;
use App\Models\AdminMenuItem;
use Illuminate\Database\Seeder;

class AdminMenuItemsSeeder extends Seeder
{
    /**
     * Seed baseline admin menu items.
     * Menu items are filtered by permissions, roles, and feature flags.
     */
    public function run(): void
    {
        // Clear existing menu items (optional - comment out if you want to keep existing)
        // AdminMenuItem::query()->delete();

        $items = [
            // Root level items
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'LayoutDashboard',
                'route_name' => 'bsadm.dashboard',
                'parent_id' => null,
                'sort_order' => 1,
                'required_permissions' => [
                    P::PlatformAdminDashboardView->value,
                    P::AdminDashboardView->value, // Legacy
                ],
                'required_roles' => null,
                'required_flags' => ['admin.dashboard'],
                'is_enabled' => true,
            ],
            [
                'key' => 'identity-access',
                'label' => 'Identity & Access',
                'icon' => 'Shield',
                'route_name' => null,
                'parent_id' => null,
                'sort_order' => 2,
                'required_permissions' => null, // Parent group - no direct permission
                'required_roles' => null,
                'required_flags' => null,
                'is_enabled' => true,
            ],
            [
                'key' => 'settings',
                'label' => 'Settings',
                'icon' => 'Settings',
                'route_name' => null, // TODO: Add settings route
                'parent_id' => null,
                'sort_order' => 10,
                'required_permissions' => [
                    P::PlatformAdminSettingsView->value,
                    P::AdminSettingsView->value, // Legacy
                ],
                'required_roles' => null,
                'required_flags' => ['admin.settings'],
                'is_enabled' => true,
            ],
        ];

        // Create root items first
        $createdItems = [];
        foreach ($items as $item) {
            $createdItems[$item['key']] = AdminMenuItem::updateOrCreate(
                ['key' => $item['key']],
                $item
            );
        }

        // Child items (under "Identity & Access")
        $childItems = [
            [
                'key' => 'users',
                'label' => 'Users',
                'icon' => 'Users',
                'route_name' => 'bsadm.users.index',
                'parent_id' => $createdItems['identity-access']->id,
                'sort_order' => 1,
                'required_permissions' => [
                    P::PlatformAdminUsersView->value,
                    P::AdminUsersView->value, // Legacy
                ],
                'required_roles' => null,
                'required_flags' => ['admin.users'],
                'is_enabled' => true,
            ],
            [
                'key' => 'roles',
                'label' => 'Roles',
                'icon' => 'UserCog',
                'route_name' => 'bsadm.rbac', // Using existing rbac route
                'parent_id' => $createdItems['identity-access']->id,
                'sort_order' => 2,
                'required_permissions' => [
                    P::PlatformAdminRolesView->value,
                    P::AdminRolesView->value, // Legacy
                ],
                'required_roles' => null,
                'required_flags' => ['admin.roles'],
                'is_enabled' => true,
            ],
            [
                'key' => 'permissions',
                'label' => 'Permissions',
                'icon' => 'Key',
                'route_name' => null, // TODO: Add permissions route
                'parent_id' => $createdItems['identity-access']->id,
                'sort_order' => 3,
                'required_permissions' => [
                    P::PlatformAdminPermissionsView->value,
                    P::AdminPermissionsView->value, // Legacy
                ],
                'required_roles' => null,
                'required_flags' => ['admin.permissions'],
                'is_enabled' => true,
            ],
            [
                'key' => 'tenants',
                'label' => 'Tenants',
                'icon' => 'Building2',
                'route_name' => null, // TODO: Add tenants route
                'parent_id' => $createdItems['identity-access']->id,
                'sort_order' => 4,
                'required_permissions' => [
                    P::PlatformAdminTenantsView->value,
                ],
                'required_roles' => null,
                'required_flags' => ['admin.tenants'],
                'is_enabled' => true,
            ],
            [
                'key' => 'audit-logs',
                'label' => 'Audit Logs',
                'icon' => 'FileText',
                'route_name' => 'bsadm.audit-logs.index',
                'parent_id' => $createdItems['identity-access']->id,
                'sort_order' => 5,
                'required_permissions' => [
                    P::PlatformAdminAuditView->value,
                    P::AdminAuditView->value, // Legacy
                ],
                'required_roles' => null,
                'required_flags' => ['admin.audit'],
                'is_enabled' => true,
            ],
        ];

        foreach ($childItems as $childItem) {
            AdminMenuItem::updateOrCreate(
                ['key' => $childItem['key']],
                $childItem
            );
        }
    }
}

