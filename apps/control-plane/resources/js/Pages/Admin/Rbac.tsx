import AppShell from '@/layouts/AppShell';

export default function Rbac() {
    return (
        <AppShell title="RBAC">
            <div className="space-y-2">
                <h2 className="text-2xl font-bold text-gray-900">RBAC</h2>
                <p className="text-sm text-gray-600">
                    Trang ví dụ được bảo vệ bằng middleware <code>role:super_admin</code>.
                </p>
            </div>
        </AppShell>
    );
}


