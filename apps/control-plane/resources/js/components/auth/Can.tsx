import { ReactNode, memo, useMemo } from 'react';
import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import type { PermissionName } from '@/types/permissions.generated';

export type RoleName = string;

type CanProps = {
    permission?: PermissionName;
    any?: readonly PermissionName[];
    all?: readonly PermissionName[];
    fallback?: ReactNode;
    children: ReactNode;
};

function getAuthSets(props: PageProps) {
    const permissions = props.auth?.permissions ?? [];
    const roles = props.auth?.roles ?? [];
    return {
        permissions,
        roles,
    };
}

export const Can = memo(function Can({
    permission,
    any,
    all,
    fallback = null,
    children,
}: CanProps) {
    const page = usePage<PageProps>();

    const { permissions } = getAuthSets(page.props);
    const permissionSet = useMemo(() => new Set(permissions), [permissions]);

    const allowed = useMemo(() => {
        if (permission) return permissionSet.has(permission);
        if (any && any.length > 0) return any.some((p) => permissionSet.has(p));
        if (all && all.length > 0) return all.every((p) => permissionSet.has(p));
        return false;
    }, [permission, any, all, permissionSet]);

    return <>{allowed ? children : fallback}</>;
});

export function useCan(permission: PermissionName): boolean {
    const page = usePage<PageProps>();
    const { permissions } = getAuthSets(page.props);
    return useMemo(() => new Set(permissions).has(permission), [permissions, permission]);
}

export function useHasRole(role: RoleName): boolean {
    const page = usePage<PageProps>();
    const { roles } = getAuthSets(page.props);
    return useMemo(() => new Set(roles).has(role), [roles, role]);
}


