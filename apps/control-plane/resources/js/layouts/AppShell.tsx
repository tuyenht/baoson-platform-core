import { ReactNode } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';

interface AppShellProps {
    children: ReactNode;
    title?: string;
}

export default function AppShell({ children, title = 'Dashboard' }: AppShellProps) {
    const { admin } = usePage<PageProps>().props;
    const dashboardRouteName = admin?.routes?.dashboard ?? 'bsadm.dashboard';

    return (
        <>
            <Head title={title} />
            <div className="min-h-screen bg-gray-50">
                {/* Topbar Placeholder */}
                <header className="bg-white shadow-sm border-b border-gray-200">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center h-16">
                            <div className="flex items-center">
                                <h1 className="text-xl font-semibold text-gray-900">
                                    Baoson Platform
                                </h1>
                            </div>
                            <nav className="flex items-center space-x-4">
                                {/* Navigation items placeholder */}
                            </nav>
                        </div>
                    </div>
                </header>

                <div className="flex">
                    {/* Sidebar Placeholder */}
                    <aside className="w-64 bg-white shadow-sm border-r border-gray-200 min-h-[calc(100vh-4rem)]">
                        <nav className="p-4">
                            <ul className="space-y-2">
                                <li>
                                    <Link
                                        href={route(dashboardRouteName)}
                                        className="block px-4 py-2 text-sm font-medium text-gray-900 bg-gray-100 rounded-md hover:bg-gray-200"
                                    >
                                        Dashboard
                                    </Link>
                                </li>
                                {/* More nav items placeholder */}
                            </ul>
                        </nav>
                    </aside>

                    {/* Main Content */}
                    <main className="flex-1 p-6">
                        {children}
                    </main>
                </div>
            </div>
        </>
    );
}

