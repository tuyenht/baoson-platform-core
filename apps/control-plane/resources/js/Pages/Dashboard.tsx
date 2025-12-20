import { PageProps } from '@/types';
import AppShell from '@/layouts/AppShell';

interface DashboardProps extends PageProps {
    appName?: string;
}

export default function Dashboard({ auth, appName = 'Baoson Platform' }: DashboardProps) {
    return (
        <AppShell title="Dashboard">
            <div className="space-y-6">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Dashboard</h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Welcome to {appName} Control Plane
                    </p>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                        System Status
                    </h3>
                    <div className="space-y-2">
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-gray-600">Application</span>
                            <span className="text-sm font-medium text-green-600">
                                Running
                            </span>
                        </div>
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-gray-600">Database</span>
                            <span className="text-sm font-medium text-green-600">
                                Connected
                            </span>
                        </div>
                    </div>
                </div>

                {auth?.user && (
                    <div className="bg-white rounded-lg shadow p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-2">
                            Current User
                        </h3>
                        <p className="text-sm text-gray-600">
                            {auth.user.name} ({auth.user.email})
                        </p>
                    </div>
                )}
            </div>
        </AppShell>
    );
}


