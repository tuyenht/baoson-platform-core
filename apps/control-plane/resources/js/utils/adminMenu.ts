import type { PageProps } from '@/types';
import { PERMISSIONS, type PermissionName } from '@/types/permissions.generated';

export type AdminMenuItem = {
    key: string;
    label: string;
    routeName: string;
    requiresAny?: readonly PermissionName[];
};

export const ADMIN_MENU: readonly AdminMenuItem[] = [
    {
        key: 'dashboard',
        label: 'Dashboard',
        routeName: 'bsadm.dashboard',
        requiresAny: [PERMISSIONS.AdminMenuDashboard, PERMISSIONS.AdminDashboardView],
    },
    {
        key: 'users',
        label: 'Users',
        routeName: 'bsadm.users.index',
        requiresAny: [PERMISSIONS.AdminMenuUsers, PERMISSIONS.AdminUsersView],
    },
    {
        key: 'security',
        label: 'Security',
        routeName: 'bsadm.security.two-factor',
        // Personal security page: available for all authenticated users
    },
    {
        key: 'audit',
        label: 'Audit Logs',
        routeName: 'bsadm.audit-logs.index',
        requiresAny: [PERMISSIONS.AdminMenuAudit, PERMISSIONS.AdminAuditView],
    },
];

export function filterAdminMenu(menu: readonly AdminMenuItem[], props: PageProps): AdminMenuItem[] {
    const permissions = props.auth?.permissions ?? [];
    const permissionSet = new Set(permissions);

    return menu.filter((item) => {
        if (!item.requiresAny || item.requiresAny.length === 0) return true;
        return item.requiresAny.some((p) => permissionSet.has(p));
    });
}


