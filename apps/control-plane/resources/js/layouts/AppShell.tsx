import { ReactNode } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import { ADMIN_MENU, filterAdminMenu } from '@/utils/adminMenu';

interface AppShellProps {
    children: ReactNode;
    title?: string;
}

export default function AppShell({ children, title = 'Dashboard' }: AppShellProps) {
    const page = usePage<PageProps>();
    const { admin, auth } = page.props;
    const dashboardRouteName = admin?.routes?.dashboard ?? 'bsadm.dashboard';
    const usersRouteName = admin?.routes?.usersIndex ?? 'bsadm.users.index';
    const logoutRouteName = admin?.routes?.logout ?? 'bsadm.logout';

    const menu = filterAdminMenu(
        ADMIN_MENU.map((item) => {
            if (item.key === 'dashboard') return { ...item, routeName: dashboardRouteName };
            if (item.key === 'users') return { ...item, routeName: usersRouteName };
            if (item.key === 'security') return { ...item, routeName: admin?.routes?.twoFactorSettings ?? 'bsadm.security.two-factor' };
            return item;
        }),
        page.props
    );

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
                                {auth?.user ? (
                                    <>
                                        <span className="text-sm text-gray-600">
                                            {auth.user.name}
                                        </span>
                                        <Link
                                            href={route(logoutRouteName)}
                                            method="post"
                                            as="button"
                                            className="text-sm font-medium text-gray-900 hover:underline"
                                        >
                                            Đăng xuất
                                        </Link>
                                    </>
                                ) : null}
                            </nav>
                        </div>
                    </div>
                </header>

                <div className="flex">
                    {/* Sidebar Placeholder */}
                    <aside className="w-64 bg-white shadow-sm border-r border-gray-200 min-h-[calc(100vh-4rem)]">
                        <nav className="p-4">
                            <ul className="space-y-2">
                                {menu.map((item) => (
                                    <li key={item.key}>
                                        <Link
                                            href={route(item.routeName)}
                                            className="block px-4 py-2 text-sm font-medium text-gray-900 bg-gray-100 rounded-md hover:bg-gray-200"
                                        >
                                            {item.label}
                                        </Link>
                                    </li>
                                ))}
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

