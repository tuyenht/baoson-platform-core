<?php

namespace App\Enums;

/**
 * Canonical permission names (DB-driven).
 *
 * Convention: "<scope>.<domain>.<resource>.<action>"
 * Where scope is one of: platform, tenant, site
 *
 * Examples:
 * - platform.admin.users.view (platform-level)
 * - site.content.posts.create (site-level)
 * - site.i18n.translations.manage (site-level)
 *
 * Legacy permissions (without scope) are kept for backward compatibility:
 * - admin.users.view (treated as platform-level)
 */
enum Permission: string
{
    // ============================================
    // PLATFORM-LEVEL PERMISSIONS (Global)
    // ============================================

    // Platform: Dashboard
    case PlatformAdminDashboardView = 'platform.admin.dashboard.view';

    // Platform: Users
    case PlatformAdminUsersView = 'platform.admin.users.view';
    case PlatformAdminUsersCreate = 'platform.admin.users.create';
    case PlatformAdminUsersUpdate = 'platform.admin.users.update';
    case PlatformAdminUsersDelete = 'platform.admin.users.delete';

    // Platform: Tenants
    case PlatformAdminTenantsView = 'platform.admin.tenants.view';
    case PlatformAdminTenantsCreate = 'platform.admin.tenants.create';
    case PlatformAdminTenantsUpdate = 'platform.admin.tenants.update';
    case PlatformAdminTenantsDelete = 'platform.admin.tenants.delete';
    case PlatformAdminTenantsManage = 'platform.admin.tenants.manage';

    // Platform: Roles
    case PlatformAdminRolesView = 'platform.admin.roles.view';
    case PlatformAdminRolesCreate = 'platform.admin.roles.create';
    case PlatformAdminRolesUpdate = 'platform.admin.roles.update';
    case PlatformAdminRolesDelete = 'platform.admin.roles.delete';

    // Platform: Permissions
    case PlatformAdminPermissionsView = 'platform.admin.permissions.view';
    case PlatformAdminPermissionsCreate = 'platform.admin.permissions.create';
    case PlatformAdminPermissionsUpdate = 'platform.admin.permissions.update';
    case PlatformAdminPermissionsDelete = 'platform.admin.permissions.delete';

    // Platform: Settings
    case PlatformAdminSettingsView = 'platform.admin.settings.view';
    case PlatformAdminSettingsUpdate = 'platform.admin.settings.update';

    // Platform: Audit logs
    case PlatformAdminAuditView = 'platform.admin.audit.view';

    // Platform: Menu
    case PlatformAdminMenuDashboard = 'platform.admin.menu.dashboard';
    case PlatformAdminMenuUsers = 'platform.admin.menu.users';
    case PlatformAdminMenuTenants = 'platform.admin.menu.tenants';
    case PlatformAdminMenuRoles = 'platform.admin.menu.roles';
    case PlatformAdminMenuPermissions = 'platform.admin.menu.permissions';
    case PlatformAdminMenuSettings = 'platform.admin.menu.settings';
    case PlatformAdminMenuAudit = 'platform.admin.menu.audit';

    // ============================================
    // SITE-LEVEL PERMISSIONS
    // ============================================

    // Site: Dashboard
    case SiteDashboardView = 'site.dashboard.view';

    // Site: Content
    case SiteContentPostsView = 'site.content.posts.view';
    case SiteContentPostsCreate = 'site.content.posts.create';
    case SiteContentPostsUpdate = 'site.content.posts.update';
    case SiteContentPostsDelete = 'site.content.posts.delete';
    case SiteContentPostsPublish = 'site.content.posts.publish';

    // Site: Media
    case SiteMediaUpload = 'site.media.upload';
    case SiteMediaView = 'site.media.view';
    case SiteMediaDelete = 'site.media.delete';

    // Site: i18n / Translations
    case SiteI18nTranslationsView = 'site.i18n.translations.view';
    case SiteI18nTranslationsManage = 'site.i18n.translations.manage';

    // Site: Settings
    case SiteSettingsView = 'site.settings.view';
    case SiteSettingsUpdate = 'site.settings.update';

    // Site: Menu
    case SiteMenuDashboard = 'site.menu.dashboard';
    case SiteMenuContent = 'site.menu.content';
    case SiteMenuMedia = 'site.menu.media';
    case SiteMenuI18n = 'site.menu.i18n';
    case SiteMenuSettings = 'site.menu.settings';

    // ============================================
    // LEGACY PERMISSIONS (Backward compatibility)
    // ============================================

    // Dashboard
    case AdminDashboardView = 'admin.dashboard.view';

    // Users
    case AdminUsersView = 'admin.users.view';
    case AdminUsersCreate = 'admin.users.create';
    case AdminUsersUpdate = 'admin.users.update';
    case AdminUsersDelete = 'admin.users.delete';

    // Roles
    case AdminRolesView = 'admin.roles.view';
    case AdminRolesCreate = 'admin.roles.create';
    case AdminRolesUpdate = 'admin.roles.update';
    case AdminRolesDelete = 'admin.roles.delete';

    // Permissions
    case AdminPermissionsView = 'admin.permissions.view';
    case AdminPermissionsCreate = 'admin.permissions.create';
    case AdminPermissionsUpdate = 'admin.permissions.update';
    case AdminPermissionsDelete = 'admin.permissions.delete';

    // Settings
    case AdminSettingsView = 'admin.settings.view';
    case AdminSettingsUpdate = 'admin.settings.update';

    // Audit logs
    case AdminAuditView = 'admin.audit.view';

    // UI / menu (frontend guards)
    case AdminMenuDashboard = 'admin.menu.dashboard';
    case AdminMenuUsers = 'admin.menu.users';
    case AdminMenuRoles = 'admin.menu.roles';
    case AdminMenuPermissions = 'admin.menu.permissions';
    case AdminMenuSettings = 'admin.menu.settings';
    case AdminMenuAudit = 'admin.menu.audit';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $p) => $p->value,
            self::cases()
        );
    }
}


