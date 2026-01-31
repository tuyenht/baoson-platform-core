import type { ComponentType } from 'react';
import Forbidden from '@/Pages/Errors/Forbidden';
import { useCan } from './Can';
import type { PermissionName } from '@/types/permissions.generated';

export function withPermission<P extends Record<string, unknown>>(
    Component: ComponentType<P>,
    permission: PermissionName
): ComponentType<P> {
    function Wrapped(props: P) {
        const allowed = useCan(permission);
        if (!allowed) return <Forbidden message={`Missing permission: ${permission}`} />;
        return <Component {...props} />;
    }

    Wrapped.displayName = `withPermission(${Component.displayName ?? Component.name ?? 'Component'})`;

    return Wrapped;
}


