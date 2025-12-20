import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function Welcome({ auth }: PageProps) {
    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen bg-gray-100">
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <h1 className="text-4xl font-bold text-gray-900 mb-4">
                            Baoson Platform Core
                        </h1>
                        <p className="text-gray-600 mb-2">
                            Laravel 12 + Inertia + React + TypeScript
                        </p>
                        {auth?.user && (
                            <p className="text-sm text-gray-500 mt-4">
                                Welcome, {auth.user.name}!
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

