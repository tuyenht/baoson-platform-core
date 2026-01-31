import AppShell from '@/layouts/AppShell';
import { Link, useForm } from '@inertiajs/react';
import type { PageProps } from '@/types';

type UserPayload = {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
    roles: string[];
};

type Props = PageProps<{ user: UserPayload; roles: string[] }>;

type FormData = {
    name: string;
    email: string;
    password: string;
    roles: string[];
};

export default function UsersEdit({ user, roles }: Props) {
    const form = useForm<FormData>({
        name: user.name,
        email: user.email,
        password: '',
        roles: user.roles ?? [],
    });

    const toggleRole = (role: string) => {
        form.setData('roles', form.data.roles.includes(role) ? form.data.roles.filter((r) => r !== role) : [...form.data.roles, role]);
    };

    return (
        <AppShell title="Edit User">
            <div className="flex items-center justify-between">
                <h2 className="text-2xl font-bold text-gray-900">Sửa user</h2>
                <Link href={route('bsadm.users.index')} className="text-sm text-slate-900 hover:underline">
                    Quay lại
                </Link>
            </div>

            <form
                className="mt-6 bg-white rounded-lg shadow p-6 space-y-4 max-w-xl"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put(route('bsadm.users.update', { user: user.id }));
                }}
            >
                <div>
                    <label className="block text-sm font-medium text-gray-700">Name</label>
                    <input
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                    />
                    {form.errors.name ? <p className="mt-1 text-sm text-red-700">{form.errors.name}</p> : null}
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                    />
                    {form.errors.email ? <p className="mt-1 text-sm text-red-700">{form.errors.email}</p> : null}
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">Password (để trống nếu không đổi)</label>
                    <input
                        type="password"
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                        value={form.data.password}
                        onChange={(e) => form.setData('password', e.target.value)}
                    />
                    {form.errors.password ? <p className="mt-1 text-sm text-red-700">{form.errors.password}</p> : null}
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">Roles</label>
                    <div className="mt-2 grid grid-cols-2 gap-2">
                        {roles.map((r) => (
                            <label key={r} className="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" checked={form.data.roles.includes(r)} onChange={() => toggleRole(r)} />
                                <span>{r}</span>
                            </label>
                        ))}
                    </div>
                </div>

                <div className="pt-2 flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="inline-flex items-center px-4 py-2 rounded-md bg-slate-900 text-white text-sm font-medium hover:bg-slate-700 disabled:opacity-50"
                    >
                        Lưu
                    </button>
                    <Link href={route('bsadm.users.index')} className="text-sm text-gray-600 hover:underline">
                        Huỷ
                    </Link>
                </div>
            </form>
        </AppShell>
    );
}


