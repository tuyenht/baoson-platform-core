import AppShell from '@/layouts/AppShell';

export default function PermissionsIndex() {
    return (
        <AppShell title="Permissions">
            <div className="max-w-2xl">
                <h2 className="text-2xl font-bold text-gray-900">Permissions</h2>
                <p className="mt-2 text-sm text-gray-600">
                    Trang placeholder để verify RBAC (`admin.permissions.view`). UI quản trị permissions sẽ được bổ sung sau.
                </p>
            </div>
        </AppShell>
    );
}


