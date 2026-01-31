import AppShell from '@/layouts/AppShell';

export default function RolesIndex() {
    return (
        <AppShell title="Roles">
            <div className="max-w-2xl">
                <h2 className="text-2xl font-bold text-gray-900">Roles</h2>
                <p className="mt-2 text-sm text-gray-600">
                    Trang placeholder để verify RBAC (`admin.roles.view`). UI quản trị roles sẽ được bổ sung sau.
                </p>
            </div>
        </AppShell>
    );
}


