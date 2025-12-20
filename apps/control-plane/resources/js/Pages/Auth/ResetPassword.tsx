import { FormEvent } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '@/layouts/AuthLayout';
import type { PageProps } from '@/types';

interface ResetPasswordProps extends PageProps {
    token: string;
    email: string;
}

type ResetPasswordForm = {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
};

export default function ResetPassword() {
    const { admin, flash, token, email } = usePage<ResetPasswordProps>().props;

    const login = admin?.routes?.login ?? 'bsadm.login';
    const passwordUpdate = admin?.routes?.passwordUpdate ?? 'bsadm.password.update';

    const { data, setData, post, processing, errors } = useForm<ResetPasswordForm>({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post(route(passwordUpdate), { preserveScroll: true });
    }

    return (
        <AuthLayout
            title="Đặt lại mật khẩu"
            subtitle="Tạo mật khẩu mới cho tài khoản của bạn"
        >
            {flash?.status ? (
                <div className="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800">
                    {flash.status}
                </div>
            ) : null}

            {flash?.error ? (
                <div className="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">
                    {flash.error}
                </div>
            ) : null}

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700">
                        Email
                    </label>
                    <input
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20"
                        autoComplete="email"
                        required
                    />
                    {errors.email ? (
                        <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                    ) : null}
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">
                        Mật khẩu mới
                    </label>
                    <input
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20"
                        autoComplete="new-password"
                        required
                    />
                    {errors.password ? (
                        <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                    ) : null}
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">
                        Xác nhận mật khẩu
                    </label>
                    <input
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        className="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/20"
                        autoComplete="new-password"
                        required
                    />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50"
                >
                    {processing ? 'Đang cập nhật...' : 'Cập nhật mật khẩu'}
                </button>
            </form>

            <div className="mt-6 text-center text-sm text-gray-600">
                <Link
                    href={route(login)}
                    className="font-medium text-gray-900 hover:underline"
                >
                    Quay lại đăng nhập
                </Link>
            </div>
        </AuthLayout>
    );
}


