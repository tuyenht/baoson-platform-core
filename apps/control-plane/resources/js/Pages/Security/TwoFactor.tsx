import { FormEvent, useEffect, useMemo, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AppShell from '@/layouts/AppShell';
import type { PageProps } from '@/types';
import Input from '@/components/Input';
import { useLocale } from '@/contexts/LocaleContext';
import { useI18n } from '@/hooks/useI18n';

type TwoFactorProps = PageProps<{
    twoFactor: {
        enabled: boolean;
        pending: boolean;
        secret: string | null;
        otpauthUrl: string | null;
        recoveryCodes: string[] | null;
    };
}>;

export default function TwoFactor() {
    const page = usePage<TwoFactorProps>();
    const { admin, flash, twoFactor, errors: pageErrors } = page.props;
    const { locale: contextLocale } = useLocale();
    const { t } = useI18n();

    const [currentLocale, setCurrentLocale] = useState(contextLocale);
    useEffect(() => {
        if (contextLocale !== currentLocale) setCurrentLocale(contextLocale);
    }, [contextLocale, currentLocale]);

    const [qrDataUrl, setQrDataUrl] = useState<string | null>(null);
    useEffect(() => {
        let cancelled = false;
        async function run() {
            if (!twoFactor?.otpauthUrl) {
                setQrDataUrl(null);
                return;
            }
            const mod = await import('qrcode');
            const dataUrl = await mod.toDataURL(twoFactor.otpauthUrl, {
                margin: 1,
                width: 180,
            });
            if (!cancelled) setQrDataUrl(dataUrl);
        }
        run().catch(() => setQrDataUrl(null));
        return () => {
            cancelled = true;
        };
    }, [twoFactor?.otpauthUrl]);

    const enableRoute = admin?.routes?.twoFactorEnable ?? 'bsadm.security.two-factor.enable';
    const confirmRoute = admin?.routes?.twoFactorConfirm ?? 'bsadm.security.two-factor.confirm';
    const disableRoute = admin?.routes?.twoFactorDisable ?? 'bsadm.security.two-factor.disable';
    const recoveryRoute = admin?.routes?.twoFactorRecovery ?? 'bsadm.security.two-factor.recovery';

    const enableForm = useForm<{ password: string; locale: string }>({
        password: '',
        locale: currentLocale,
    });
    const confirmForm = useForm<{ code: string; locale: string }>({
        code: '',
        locale: currentLocale,
    });
    const disableForm = useForm<{ password: string; locale: string }>({
        password: '',
        locale: currentLocale,
    });
    const regenForm = useForm<{ password: string; locale: string }>({
        password: '',
        locale: currentLocale,
    });

    useEffect(() => {
        enableForm.setData('locale', currentLocale);
        confirmForm.setData('locale', currentLocale);
        disableForm.setData('locale', currentLocale);
        regenForm.setData('locale', currentLocale);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [currentLocale]);

    const alertMessage = useMemo(() => {
        return (
            flash?.error ||
            enableForm.errors.password ||
            disableForm.errors.password ||
            regenForm.errors.password ||
            confirmForm.errors.code ||
            pageErrors?.password ||
            pageErrors?.code ||
            null
        );
    }, [
        confirmForm.errors.code,
        disableForm.errors.password,
        enableForm.errors.password,
        flash?.error,
        pageErrors,
        regenForm.errors.password,
    ]);

    function submitEnable(e: FormEvent) {
        e.preventDefault();
        enableForm.post(route(enableRoute), { preserveScroll: true, preserveState: true });
    }

    function submitConfirm(e: FormEvent) {
        e.preventDefault();
        confirmForm.post(route(confirmRoute), { preserveScroll: true, preserveState: true });
    }

    function submitDisable(e: FormEvent) {
        e.preventDefault();
        disableForm.post(route(disableRoute), { preserveScroll: true, preserveState: true });
    }

    function submitRegen(e: FormEvent) {
        e.preventDefault();
        regenForm.post(route(recoveryRoute), { preserveScroll: true, preserveState: true });
    }

    return (
        <AppShell title="Bảo mật">
            <Head title="Bảo mật" />

            <div className="max-w-3xl space-y-4">
                <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                    <h2 className="text-lg font-semibold text-gray-900">Xác minh 2 bước (Email + Mật khẩu)</h2>
                    <p className="mt-1 text-sm text-gray-600">
                        Bật 2 bước để tăng bảo mật. Khi đăng nhập bằng email + mật khẩu, hệ thống sẽ yêu cầu thêm mã OTP.
                    </p>
                </div>

                {alertMessage && (
                    <div className="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 transition-all duration-300">
                        {alertMessage}
                    </div>
                )}
                {flash?.status && (
                    <div className="rounded-xl bg-green-50 px-4 py-3 text-sm text-green-800 transition-all duration-300">
                        {flash.status}
                    </div>
                )}

                {!twoFactor.enabled && !twoFactor.pending ? (
                    <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <h3 className="text-base font-semibold text-gray-900">Bật xác minh 2 bước</h3>
                        <form onSubmit={submitEnable} className="mt-4 space-y-4">
                            <Input
                                label="Mật khẩu hiện tại"
                                placeholder="••••••••"
                                isPassword
                                value={enableForm.data.password}
                                onChange={(e) => enableForm.setData('password', e.target.value)}
                                required
                                disabled={enableForm.processing}
                            />

                            <button
                                type="submit"
                                disabled={enableForm.processing}
                                className={`
                                    w-full md:w-auto px-5 py-3 rounded-xl font-semibold text-[15px]
                                    shadow-lg shadow-blue-500/20
                                    flex items-center justify-center gap-2 transition-all duration-200
                                    ${enableForm.processing
                                        ? 'bg-blue-700 text-white/90 cursor-wait scale-[0.98] shadow-inner'
                                        : 'bg-blue-600 text-white hover:bg-blue-700 hover:-translate-y-0.5'}
                                `}
                            >
                                {enableForm.processing ? (
                                    <>
                                        <span>{t('auth.processing')}</span>
                                    </>
                                ) : (
                                    <>
                                        <span>Bắt đầu thiết lập</span>
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </>
                                )}
                            </button>
                        </form>
                    </div>
                ) : null}

                {!twoFactor.enabled && twoFactor.pending ? (
                    <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <h3 className="text-base font-semibold text-gray-900">Xác nhận thiết lập</h3>
                        <p className="mt-1 text-sm text-gray-600">
                            Quét QR bằng Google Authenticator/Microsoft Authenticator, sau đó nhập mã OTP để xác nhận.
                        </p>

                        <div className="mt-4 flex flex-col md:flex-row gap-6 items-start">
                            <div className="shrink-0">
                                {qrDataUrl ? (
                                    <img
                                        src={qrDataUrl}
                                        alt="2FA QR"
                                        className="w-[180px] h-[180px] rounded-lg border border-gray-200"
                                    />
                                ) : (
                                    <div className="w-[180px] h-[180px] rounded-lg border border-gray-200 flex items-center justify-center text-xs text-gray-500">
                                        QR đang tải...
                                    </div>
                                )}
                            </div>
                            <div className="flex-1">
                                <div className="text-sm text-gray-700">
                                    <div className="font-medium">Mã bí mật (manual):</div>
                                    <div className="mt-1 font-mono text-xs bg-gray-50 border border-gray-200 rounded-lg p-3 break-all">
                                        {twoFactor.secret}
                                    </div>
                                </div>

                                <form onSubmit={submitConfirm} className="mt-4 space-y-4">
                                    <Input
                                        label="Mã OTP"
                                        placeholder="123 456"
                                        type="text"
                                        value={confirmForm.data.code}
                                        onChange={(e) => confirmForm.setData('code', e.target.value)}
                                        required
                                        disabled={confirmForm.processing}
                                    />

                                    <button
                                        type="submit"
                                        disabled={confirmForm.processing}
                                        className={`
                                            w-full md:w-auto px-5 py-3 rounded-xl font-semibold text-[15px]
                                            shadow-lg shadow-blue-500/20
                                            flex items-center justify-center gap-2 transition-all duration-200
                                            ${confirmForm.processing
                                                ? 'bg-blue-700 text-white/90 cursor-wait scale-[0.98] shadow-inner'
                                                : 'bg-blue-600 text-white hover:bg-blue-700 hover:-translate-y-0.5'}
                                        `}
                                    >
                                        {confirmForm.processing ? (
                                            <span>{t('auth.processing')}</span>
                                        ) : (
                                            <>
                                                <span>Xác nhận</span>
                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </>
                                        )}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                ) : null}

                {twoFactor.enabled ? (
                    <div className="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <h3 className="text-base font-semibold text-gray-900">Đã bật xác minh 2 bước</h3>

                        {twoFactor.recoveryCodes && twoFactor.recoveryCodes.length > 0 ? (
                            <div className="mt-4">
                                <div className="text-sm font-medium text-gray-900">Mã khôi phục (lưu lại ngay):</div>
                                <ul className="mt-2 grid grid-cols-2 gap-2">
                                    {twoFactor.recoveryCodes.map((c) => (
                                        <li key={c} className="font-mono text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                                            {c}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ) : null}

                        <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <form onSubmit={submitRegen} className="space-y-3">
                                <div className="text-sm font-medium text-gray-900">Tạo lại mã khôi phục</div>
                                <Input
                                    label="Mật khẩu"
                                    placeholder="••••••••"
                                    isPassword
                                    value={regenForm.data.password}
                                    onChange={(e) => regenForm.setData('password', e.target.value)}
                                    required
                                    disabled={regenForm.processing}
                                />
                                <button
                                    type="submit"
                                    disabled={regenForm.processing}
                                    className={`
                                        w-full px-5 py-3 rounded-xl font-semibold text-[15px]
                                        shadow-lg shadow-blue-500/20
                                        flex items-center justify-center gap-2 transition-all duration-200
                                        ${regenForm.processing
                                            ? 'bg-blue-700 text-white/90 cursor-wait scale-[0.98] shadow-inner'
                                            : 'bg-blue-600 text-white hover:bg-blue-700 hover:-translate-y-0.5'}
                                    `}
                                >
                                    {regenForm.processing ? <span>{t('auth.processing')}</span> : <span>Tạo lại</span>}
                                </button>
                            </form>

                            <form onSubmit={submitDisable} className="space-y-3">
                                <div className="text-sm font-medium text-gray-900">Tắt xác minh 2 bước</div>
                                <Input
                                    label="Mật khẩu"
                                    placeholder="••••••••"
                                    isPassword
                                    value={disableForm.data.password}
                                    onChange={(e) => disableForm.setData('password', e.target.value)}
                                    required
                                    disabled={disableForm.processing}
                                />
                                <button
                                    type="submit"
                                    disabled={disableForm.processing}
                                    className={`
                                        w-full px-5 py-3 rounded-xl font-semibold text-[15px]
                                        shadow-lg shadow-red-500/20
                                        flex items-center justify-center gap-2 transition-all duration-200
                                        ${disableForm.processing
                                            ? 'bg-red-700 text-white/90 cursor-wait scale-[0.98] shadow-inner'
                                            : 'bg-red-600 text-white hover:bg-red-700 hover:-translate-y-0.5'}
                                    `}
                                >
                                    {disableForm.processing ? <span>{t('auth.processing')}</span> : <span>Tắt</span>}
                                </button>
                            </form>
                        </div>
                    </div>
                ) : null}
            </div>
        </AppShell>
    );
}


