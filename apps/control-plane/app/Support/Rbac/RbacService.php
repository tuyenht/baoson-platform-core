<?php

namespace App\Support\Rbac;

use App\Enums\Permission as P;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RbacService
{
    /**
     * Sync canonical permissions into DB and ensure default roles exist.
     *
     * @return array{created_permissions:int, created_roles:int}
     */
    public static function sync(bool $assignAllToSuperAdmin = true): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = (string) config('auth.defaults.guard', 'web');

        $createdPermissions = 0;
        foreach (P::values() as $name) {
            $permission = Permission::query()
                ->where('name', $name)
                ->where('guard_name', $guard)
                ->first();

            if (! $permission) {
                Permission::create(['name' => $name, 'guard_name' => $guard]);
                $createdPermissions++;
            }
        }

        $createdRoles = 0;
        $roleNames = array_keys((array) config('rbac.roles', []));

        /** @var array<string, Role> $roles */
        $roles = [];
        foreach ($roleNames as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', $guard)
                ->first();

            if (! $role) {
                $role = Role::create(['name' => $roleName, 'guard_name' => $guard]);
                $createdRoles++;
            }

            $roles[$roleName] = $role;
        }

        // ============================================
        // PLATFORM-LEVEL ROLES
        // ============================================

        // saasOwner: Full platform access (all permissions)
        if (isset($roles['saasOwner'])) {
            $roles['saasOwner']->syncPermissions(P::values());
        }

        // saasAdmin: Platform management (except sensitive operations)
        if (isset($roles['saasAdmin'])) {
            $roles['saasAdmin']->syncPermissions([
                // Platform admin
                P::PlatformAdminDashboardView->value,
                P::PlatformAdminUsersView->value,
                P::PlatformAdminUsersCreate->value,
                P::PlatformAdminUsersUpdate->value,
                P::PlatformAdminUsersDelete->value,
                P::PlatformAdminTenantsView->value,
                P::PlatformAdminTenantsCreate->value,
                P::PlatformAdminTenantsUpdate->value,
                P::PlatformAdminSettingsView->value,
                P::PlatformAdminSettingsUpdate->value,
                P::PlatformAdminAuditView->value,

                // Platform menu
                P::PlatformAdminMenuDashboard->value,
                P::PlatformAdminMenuUsers->value,
                P::PlatformAdminMenuTenants->value,
                P::PlatformAdminMenuSettings->value,
                P::PlatformAdminMenuAudit->value,

                // Legacy (backward compatibility)
                P::AdminDashboardView->value,
                P::AdminUsersView->value,
                P::AdminUsersCreate->value,
                P::AdminUsersUpdate->value,
                P::AdminUsersDelete->value,
                P::AdminSettingsView->value,
                P::AdminSettingsUpdate->value,
                P::AdminAuditView->value,
                P::AdminMenuDashboard->value,
                P::AdminMenuUsers->value,
                P::AdminMenuSettings->value,
                P::AdminMenuAudit->value,
            ]);
        }

        // ============================================
        // SITE-LEVEL ROLES
        // ============================================

        // siteOwner: Full site access + can manage site settings
        if (isset($roles['siteOwner'])) {
            $roles['siteOwner']->syncPermissions([
                // Site dashboard
                P::SiteDashboardView->value,
                P::SiteMenuDashboard->value,

                // Site content (full access)
                P::SiteContentPostsView->value,
                P::SiteContentPostsCreate->value,
                P::SiteContentPostsUpdate->value,
                P::SiteContentPostsDelete->value,
                P::SiteContentPostsPublish->value,
                P::SiteMenuContent->value,

                // Site media
                P::SiteMediaUpload->value,
                P::SiteMediaView->value,
                P::SiteMediaDelete->value,
                P::SiteMenuMedia->value,

                // Site i18n
                P::SiteI18nTranslationsView->value,
                P::SiteI18nTranslationsManage->value,
                P::SiteMenuI18n->value,

                // Site settings
                P::SiteSettingsView->value,
                P::SiteSettingsUpdate->value,
                P::SiteMenuSettings->value,
            ]);
        }

        // siteAdmin: Site management (can publish, manage content, but not delete site)
        if (isset($roles['siteAdmin'])) {
            $roles['siteAdmin']->syncPermissions([
                P::SiteDashboardView->value,
                P::SiteMenuDashboard->value,

                P::SiteContentPostsView->value,
                P::SiteContentPostsCreate->value,
                P::SiteContentPostsUpdate->value,
                P::SiteContentPostsDelete->value,
                P::SiteContentPostsPublish->value,
                P::SiteMenuContent->value,

                P::SiteMediaUpload->value,
                P::SiteMediaView->value,
                P::SiteMediaDelete->value,
                P::SiteMenuMedia->value,

                P::SiteSettingsView->value,
                P::SiteSettingsUpdate->value,
                P::SiteMenuSettings->value,
            ]);
        }

        // siteEditor: Can create/edit content, but cannot publish
        if (isset($roles['siteEditor'])) {
            $roles['siteEditor']->syncPermissions([
                P::SiteDashboardView->value,
                P::SiteMenuDashboard->value,

                P::SiteContentPostsView->value,
                P::SiteContentPostsCreate->value,
                P::SiteContentPostsUpdate->value,
                P::SiteMenuContent->value,

                P::SiteMediaUpload->value,
                P::SiteMediaView->value,
                P::SiteMenuMedia->value,
            ]);
        }

        // siteTranslator: Can manage translations only
        if (isset($roles['siteTranslator'])) {
            $roles['siteTranslator']->syncPermissions([
                P::SiteDashboardView->value,
                P::SiteMenuDashboard->value,

                P::SiteI18nTranslationsView->value,
                P::SiteI18nTranslationsManage->value,
                P::SiteMenuI18n->value,
            ]);
        }

        // siteViewer: Read-only access
        if (isset($roles['siteViewer'])) {
            $roles['siteViewer']->syncPermissions([
                P::SiteDashboardView->value,
                P::SiteMenuDashboard->value,

                P::SiteContentPostsView->value,
                P::SiteMediaView->value,
                P::SiteI18nTranslationsView->value,
                P::SiteSettingsView->value,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'created_permissions' => $createdPermissions,
            'created_roles' => $createdRoles,
        ];
    }
}


