import { FormEvent, useEffect, useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '@/layouts/AuthLayout';
import { useI18n } from '@/hooks/useI18n';
import { useLocale } from '@/contexts/LocaleContext';
import type { PageProps } from '@/types';
import Input from '@/components/Input';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
    locale: string;
};

export default function Login() {
    const { admin, flash, social, errors: pageErrors } = usePage<PageProps>().props;
    const { locale: contextLocale } = useLocale();
    const { t } = useI18n();
    
    // Local state to track locale and force re-render
    const [currentLocale, setCurrentLocale] = useState(contextLocale);
    
    // Subscribe to locale changes and force re-render
    useEffect(() => {
        if (contextLocale !== currentLocale) {
            setCurrentLocale(contextLocale);
        }
    }, [contextLocale, currentLocale]);

    const loginStore = admin?.routes?.loginStore ?? 'bsadm.login.store';
    const passwordRequest = admin?.routes?.passwordRequest ?? 'bsadm.password.request';
    const oauthRedirect = admin?.routes?.oauthRedirect ?? 'bsadm.oauth.redirect';

    const { data, setData, post, processing, errors } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: true,
        locale: currentLocale,
    });

    // Keep form locale in sync so backend messages are localized correctly.
    useEffect(() => {
        setData('locale', currentLocale);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [currentLocale]);

    const alertMessage =
        flash?.error ||
        errors.email ||
        errors.password ||
        pageErrors?.email ||
        pageErrors?.password ||
        (Object.values(errors)[0] as string | undefined) ||
        (pageErrors ? (Object.values(pageErrors)[0] as string | undefined) : undefined) ||
        null;

    function submit(e: FormEvent) {
        e.preventDefault();
        post(route(loginStore), { preserveScroll: true, preserveState: true });
    }

    return (
        <AuthLayout title={t('auth.welcome')}>
            {/* Login Card - Match code mẫu EXACTLY */}
            <div key={`login-${currentLocale}`} className="glass w-full rounded-3xl p-6 md:p-10 shadow-2xl relative transition-all duration-500">
                {/* Header */}
                <div className="mb-6">
                    <h2 className="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 to-slate-700 tracking-tight">
                        {t('auth.welcome')}
                    </h2>
                </div>

                {flash?.status && (
                    <div className="mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-800">
                        {flash.status}
                    </div>
                )}

                <form onSubmit={submit} className="space-y-4">
                    {/* Email Input */}
                    <Input 
                        key={`email-${currentLocale}`}
                        label={t('auth.email_label')} 
                        placeholder={t('auth.email_placeholder')} 
                        type="email" 
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        disabled={processing}
                        showAtSymbol={true}
                        icon={
                            <>
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" cx="12" cy="7" r="4" />
                            </>
                        }
                    />
                    {errors.email && (
                        <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                    )}

                    {/* Password Input */}
                    <div>
                        <Input 
                            key={`password-${currentLocale}`}
                            label={t('auth.password_label')} 
                            placeholder={t('auth.password_placeholder')} 
                            isPassword
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                            disabled={processing}
                            icon={
                                <>
                                    <rect strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </>
                            }
                        />
                        {errors.password && (
                            <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                        )}
                        {/* Forgot Password Link */}
                        <div className="flex justify-end mt-2">
                            <Link
                                href={route(passwordRequest)}
                                className={`text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline transition-colors ${processing ? 'pointer-events-none opacity-50' : ''}`}
                            >
                                {t('auth.forgot_password')}
                            </Link>
                        </div>
                    </div>

                    {/* Alert banner (match mẫu: ngay trước nút submit) */}
                    {alertMessage && (
                        <div className="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 transition-all duration-300">
                            {alertMessage}
                        </div>
                    )}

                    {/* Submit Button - Match reference login-system */}
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
                                <span>{t('auth.sign_in')}</span>
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

                {/* Divider */}
                <div className="relative my-6">
                    <div className="absolute inset-0 flex items-center">
                        <div className="w-full border-t border-slate-100"></div>
                    </div>
                    <div className="relative flex justify-center">
                        <span className="bg-white px-4 text-xs font-medium text-slate-400">
                            {t('auth.or_continue')}
                        </span>
                    </div>
                </div>

                {/* Warning message - hidden by default (not in code mẫu) */}
                {!social?.socialiteInstalled && (
                    <div className="mb-3 rounded-xl bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        {t('auth.socialite_not_installed')}
                    </div>
                )}

                <div className="grid grid-cols-2 gap-3">
                    <a
                        href={route(oauthRedirect, { provider: 'google' })}
                        className={`
                            flex items-center justify-center gap-2.5 px-4 py-2.5 
                            bg-white border border-slate-200 rounded-xl 
                            hover:bg-slate-50 hover:border-slate-300 
                            transition-all duration-200 shadow-sm
                            group
                            ${processing ? 'pointer-events-none opacity-50' : ''}
                        `}
                        onClick={(e) => {
                            if (!social?.googleEnabled || processing) {
                                e.preventDefault();
                            }
                        }}
                        title={social?.googleEnabled ? t('auth.login_with_google') : t('auth.google_login_not_enabled')}
                    >
                        <svg className="w-5 h-5" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05" />
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                        </svg>
                        <span className="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors capitalize">
                            Google
                        </span>
                    </a>

                    <a
                        href={route(oauthRedirect, { provider: 'facebook' })}
                        className={`
                            flex items-center justify-center gap-2.5 px-4 py-2.5 
                            bg-white border border-slate-200 rounded-xl 
                            hover:bg-slate-50 hover:border-slate-300 
                            transition-all duration-200 shadow-sm
                            group
                            ${processing ? 'pointer-events-none opacity-50' : ''}
                        `}
                        onClick={(e) => {
                            if (!social?.facebookEnabled || processing) {
                                e.preventDefault();
                            }
                        }}
                        title={social?.facebookEnabled ? t('auth.login_with_facebook') : t('auth.facebook_login_not_enabled')}
                    >
                        <svg className="w-5 h-5 text-[#1877F2] fill-current" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        <span className="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors capitalize">
                            Facebook
                        </span>
                    </a>
                </div>
            </div>
        </AuthLayout>
    );
}
