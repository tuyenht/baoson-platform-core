import { FormEvent, useEffect, useMemo, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '@/layouts/AuthLayout';
import type { PageProps } from '@/types';
import Input from '@/components/Input';
import { useI18n } from '@/hooks/useI18n';
import { useLocale } from '@/contexts/LocaleContext';

type Props = PageProps<{
    email: string;
}>;

type FormData = {
    code: string;
    recovery_code: string;
    locale: string;
};

export default function TwoFactorChallenge() {
    const { admin, flash, errors: pageErrors } = usePage<Props>().props;
    const { locale: contextLocale } = useLocale();
    const { t } = useI18n();

    const [currentLocale, setCurrentLocale] = useState(contextLocale);
    useEffect(() => {
        if (contextLocale !== currentLocale) setCurrentLocale(contextLocale);
    }, [contextLocale, currentLocale]);

    const challengeStore =
        admin?.routes?.twoFactorChallengeStore ?? 'bsadm.two-factor.challenge.store';

    const { data, setData, post, processing, errors, reset } = useForm<FormData>({
        code: '',
        recovery_code: '',
        locale: currentLocale,
    });

    useEffect(() => {
        setData('locale', currentLocale);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [currentLocale]);

    const alertMessage = useMemo(() => {
        return (
            flash?.error ||
            errors.code ||
            errors.recovery_code ||
            pageErrors?.code ||
            pageErrors?.recovery_code ||
            (Object.values(errors)[0] as string | undefined) ||
            (pageErrors ? (Object.values(pageErrors)[0] as string | undefined) : undefined) ||
            null
        );
    }, [errors, flash?.error, pageErrors]);

    function submit(e: FormEvent) {
        e.preventDefault();
        post(route(challengeStore), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                reset('code', 'recovery_code');
            },
        });
    }

    return (
        <AuthLayout title="Two-factor">
            <Head title="Two-factor" />
            <div
                key={`twofactor-${currentLocale}`}
                className="glass w-full rounded-3xl p-6 md:p-10 shadow-2xl relative transition-all duration-500"
            >
                <div className="mb-6">
                    <h2 className="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-slate-700 tracking-tight">
                        Xác minh 2 bước
                    </h2>
                    <p className="mt-2 text-sm text-slate-600">
                        Nhập mã từ ứng dụng Authenticator để hoàn tất đăng nhập.
                    </p>
                </div>

                {flash?.status && (
                    <div className="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-800 transition-all duration-300">
                        {flash.status}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-4">
                    <Input
                        key={`code-${currentLocale}`}
                        label="Mã xác minh"
                        placeholder="123 456"
                        type="text"
                        value={data.code}
                        onChange={(e) => {
                            setData('code', e.target.value);
                            if (data.recovery_code) setData('recovery_code', '');
                        }}
                        required={false}
                        disabled={processing}
                        icon={
                            <>
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="1.5"
                                    d="M12 6v6l4 2"
                                />
                                <circle
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="1.5"
                                    cx="12"
                                    cy="12"
                                    r="9"
                                />
                            </>
                        }
                    />

                    <div className="text-xs text-slate-500">
                        Hoặc dùng <b>mã khôi phục</b> nếu bạn không thể truy cập ứng dụng.
                    </div>

                    <Input
                        key={`recovery-${currentLocale}`}
                        label="Mã khôi phục"
                        placeholder="ABCDE-FGHIJ"
                        type="text"
                        value={data.recovery_code}
                        onChange={(e) => {
                            setData('recovery_code', e.target.value.toUpperCase());
                            if (data.code) setData('code', '');
                        }}
                        required={false}
                        disabled={processing}
                        icon={
                            <>
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="1.5"
                                    d="M9 12h6"
                                />
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth="1.5"
                                    d="M10 6h4a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"
                                />
                            </>
                        }
                    />

                    {/* Alert banner (ngay trước nút submit) */}
                    {alertMessage && (
                        <div className="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 transition-all duration-300">
                            {alertMessage}
                        </div>
                    )}

                    <button
                        type="submit"
                        disabled={processing}
                        className={`
                            w-full py-3 md:py-3.5 rounded-xl font-semibold text-[15px]
                            shadow-lg shadow-blue-500/20
                            flex items-center justify-center gap-2 transition-all duration-200 mt-1 md:mt-2
                            ${processing
                                ? 'bg-blue-700 text-white/90 cursor-wait scale-[0.98] shadow-inner'
                                : 'bg-blue-600 text-white hover:bg-blue-700 hover:-translate-y-0.5'}
                        `}
                    >
                        {processing ? (
                            <>
                                <svg
                                    className="animate-spin h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        className="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        strokeWidth="4"
                                    ></circle>
                                    <path
                                        className="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                <span>{t('auth.processing')}</span>
                            </>
                        ) : (
                            <>
                                <span>Xác minh</span>
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"
                                    />
                                </svg>
                            </>
                        )}
                    </button>
                </form>
            </div>
        </AuthLayout>
    );
}


