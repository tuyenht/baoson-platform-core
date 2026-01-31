import AppShell from '@/layouts/AppShell';
import type { PageProps } from '@/types';

interface ForbiddenProps extends PageProps {
    message?: string;
}

export default function Forbidden({ message }: ForbiddenProps) {
    return (
        <AppShell title="403 Forbidden">
            <div className="max-w-2xl">
                <h2 className="text-2xl font-bold text-gray-900">403 - Forbidden</h2>
                <p className="mt-2 text-sm text-gray-600">
                    Bạn không có quyền truy cập tài nguyên này.
                </p>
                {message ? (
                    <p className="mt-4 text-sm text-gray-500">
                        Chi tiết: {message}
                    </p>
                ) : null}
            </div>
        </AppShell>
    );
}


