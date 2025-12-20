import { ReactNode } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import LanguageSwitcher from '@/components/LanguageSwitcher';

interface AuthLayoutProps {
    children: ReactNode;
    title: string;
    subtitle?: string;
}

export default function AuthLayout({ children, title, subtitle }: AuthLayoutProps) {
    const { appName, admin } = usePage<PageProps>().props;
    const dashboardRouteName = admin?.routes?.dashboard ?? 'bsadm.dashboard';

    return (
        <>
            <Head title={title} />
            <div className="min-h-[100dvh] w-full flex flex-col items-center justify-center px-8 py-6 md:p-4 animate-gradient bg-gradient-to-br from-sky-700 via-blue-600 to-slate-800 relative overflow-hidden">
                {/* Decorative Elements */}
                <div className="absolute top-20 left-20 w-64 h-64 bg-cyan-400/20 rounded-full blur-3xl animate-pulse pointer-events-none"></div>
                <div className="absolute bottom-20 right-20 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-bounce duration-[12s] pointer-events-none"></div>

                {/* Language Switcher - Responsive: Mobile centered, Desktop top-right */}
                <LanguageSwitcher />

                <main className="relative w-full max-w-sm md:max-w-md flex flex-col items-center z-10 transition-all duration-500">
                    {/* Logo - Responsive sizing: Mobile h-16, Desktop h-20 */}
                    <div className="mb-4 md:mb-8 transition-transform duration-500 hover:scale-105">
                        <Link
                            href={route(dashboardRouteName)}
                            aria-label={appName ?? 'Bao Son'}
                        >
                            <img 
                                src="https://khachhang.baoson.net/assets/img/whmcs.png" 
                                alt="Bao Son Logo" 
                                className="h-16 md:h-20 w-auto drop-shadow-2xl filter brightness-110"
                            />
                        </Link>
                    </div>

                    {/* Optional heading/subtitle area for non-login pages */}
                    {(subtitle || title) ? (
                        <div className="sr-only">
                            <h1>{title}</h1>
                            {subtitle ? <p>{subtitle}</p> : null}
                        </div>
                    ) : null}

                    {children}

                    <footer className="mt-4 md:mt-8 text-center text-white/40 text-xs font-medium tracking-wide">
                        <p>
                            © {new Date().getFullYear()}{' '}
                            <a 
                                href="https://baoson.net" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                className="hover:text-white transition-colors"
                            >
                                BaoSon Ads
                            </a>
                            . All rights reserved.
                        </p>
                    </footer>
                </main>
            </div>
        </>
    );
}
