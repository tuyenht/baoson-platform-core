import AppShell from '@/layouts/AppShell';
import type { PageProps } from '@/types';
import { useForm } from '@inertiajs/react';

type AuditLogRow = {
    id: number;
    occurred_at: string;
    event: string;
    actor_id: number | null;
    actor_email: string | null;
    ip: string | null;
    route: string | null;
    path: string | null;
    status_code: number | null;
};

type PaginationLink = { url: string | null; label: string; active: boolean };

type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
};

interface AuditLogsProps extends PageProps {
    filters: { q: string; event: string };
    logs: Paginated<AuditLogRow>;
}

export default function AuditLogsIndex({ filters, logs }: AuditLogsProps) {
    const { data, setData, get, processing } = useForm({
        q: filters.q ?? '',
        event: filters.event ?? '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        get(route('bsadm.audit-logs.index'), {
            preserveState: true,
            preserveScroll: true,
        });
    }

    return (
        <AppShell title="Audit Logs">
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold text-gray-900">Audit Logs</h2>
                    <div className="text-sm text-gray-500">Total: {logs.total}</div>
                </div>

                <form onSubmit={submit} className="bg-white rounded-lg shadow p-4 space-y-3">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label className="block text-xs font-medium text-gray-600 mb-1">Search</label>
                            <input
                                className="w-full rounded-md border border-gray-200 px-3 py-2 text-sm"
                                value={data.q}
                                onChange={(e) => setData('q', e.target.value)}
                                placeholder="event / email / ip / path"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-gray-600 mb-1">Event (exact)</label>
                            <input
                                className="w-full rounded-md border border-gray-200 px-3 py-2 text-sm"
                                value={data.event}
                                onChange={(e) => setData('event', e.target.value)}
                                placeholder="auth.login_success"
                            />
                        </div>
                        <div className="flex items-end gap-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                            >
                                Filter
                            </button>
                            <button
                                type="button"
                                className="inline-flex items-center justify-center rounded-md border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                onClick={() => {
                                    setData({ q: '', event: '' });
                                    get(route('bsadm.audit-logs.index'), { preserveState: true, preserveScroll: true });
                                }}
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </form>

                <div className="bg-white rounded-lg shadow overflow-hidden">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Time
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Event
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actor
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Route / Path
                                </th>
                                <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {logs.data.map((l) => (
                                <tr key={l.id}>
                                    <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {l.occurred_at}
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {l.event}
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {l.actor_email ?? (l.actor_id ? `#${l.actor_id}` : '-')}
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{l.ip ?? '-'}</td>
                                    <td className="px-4 py-3 text-sm text-gray-700">
                                        <div className="font-medium">{l.route ?? '-'}</div>
                                        <div className="text-gray-500">{l.path ?? '-'}</div>
                                    </td>
                                    <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        {l.status_code ?? '-'}
                                    </td>
                                </tr>
                            ))}
                            {logs.data.length === 0 && (
                                <tr>
                                    <td className="px-4 py-6 text-sm text-gray-600" colSpan={6}>
                                        No audit logs found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppShell>
    );
}


