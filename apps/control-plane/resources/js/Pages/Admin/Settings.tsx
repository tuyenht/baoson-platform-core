import { useForm } from '@inertiajs/react';
import AppShell from '@/layouts/AppShell';
import type { PageProps } from '@/types';

type Props = PageProps;

export default function Settings({ flash }: Props) {
    const { data, setData, post, processing } = useForm({
        dummy: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('bsadm.settings.update'));
    };

    return (
        <AppShell title="Settings">
            <div className="space-y-6">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900">Settings</h2>
                    {flash?.status ? (
                        <p className="mt-2 text-sm text-green-700">{flash.status}</p>
                    ) : null}
                    {flash?.error ? (
                        <p className="mt-2 text-sm text-red-700">{flash.error}</p>
                    ) : null}
                </div>

                <form onSubmit={submit} className="bg-white rounded-lg shadow p-6 space-y-4">
                    <p className="text-sm text-gray-600">
                        Trang demo để kiểm tra permission <span className="font-mono">admin.settings.*</span>.
                    </p>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Dummy value</label>
                        <input
                            value={data.dummy}
                            onChange={(e) => setData('dummy', e.target.value)}
                            className="mt-1 w-full rounded-md border-gray-300 shadow-sm"
                        />
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex items-center px-4 py-2 rounded-md bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 disabled:opacity-50"
                    >
                        Lưu
                    </button>
                </form>
            </div>
        </AppShell>
    );
}


