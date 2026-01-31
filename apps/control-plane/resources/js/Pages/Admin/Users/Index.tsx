import AppShell from '@/layouts/AppShell';
import type { PageProps } from '@/types';
import { Can } from '@/components/auth/Can';
import { withPermission } from '@/components/auth/withPermission';
import { PERMISSIONS } from '@/types/permissions.generated';

type UserRow = {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
    created_at?: string;
};

interface UsersIndexProps extends PageProps {
    users: UserRow[];
}

function UsersIndex({ users }: UsersIndexProps) {
    return (
        <AppShell title="Users">
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold text-gray-900">Users</h2>
                    <Can permission={PERMISSIONS.AdminUsersCreate}>
                        <div className="text-sm text-gray-600">
                            (Create UI placeholder)
                        </div>
                    </Can>
                </div>

                <div className="bg-white rounded-lg shadow overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Verified
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {users.map((u) => (
                                <tr key={u.id}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {u.name}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {u.email}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {u.email_verified_at ? 'Yes' : 'No'}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppShell>
    );
}

export default withPermission(UsersIndex, PERMISSIONS.AdminUsersView);


