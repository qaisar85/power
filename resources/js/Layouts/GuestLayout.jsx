import React from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function GuestLayout({ children, title = 'Welcome' }) {
    const { disclaimer: globalDisclaimer } = usePage().props;
    return (
        <div className="min-h-screen bg-gray-100 flex flex-col items-center pt-6 sm:justify-center sm:pt-0">
            <div className="mb-4">
                <Link href="/">
                    <div className="h-16 w-16 flex items-center justify-center rounded-full bg-indigo-600 text-white font-bold text-xl">
                        WOP
                    </div>
                </Link>
            </div>
            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {children}
            </div>
            {/* Global disclaimer for guest pages */}
            <div className="w-full sm:max-w-md mt-4 px-4 py-2 bg-yellow-50 border border-yellow-200 text-xs text-yellow-800 text-center rounded">
                {globalDisclaimer || 'Investments involve risk. This is not a public offer.'}
            </div>
        </div>
    );
}