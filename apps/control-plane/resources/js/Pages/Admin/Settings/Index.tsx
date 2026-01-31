import AppShell from '@/layouts/AppShell';

export default function SettingsIndex() {
    return (
        <AppShell title="Settings">
            <div className="max-w-2xl">
                <h2 className="text-2xl font-bold text-gray-900">Settings</h2>
                <p className="mt-2 text-sm text-gray-600">
                    Trang placeholder để verify RBAC (`admin.settings.view`). UI cấu hình sẽ được bổ sung sau.
                </p>
            </div>
        </AppShell>
    );
}


