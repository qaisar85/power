import React from 'react';
import { Link } from '@inertiajs/react';

export default function SiteFooter() {
    return (
        <footer className="bg-white border-t">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p className="text-sm text-gray-600">
                            © {new Date().getFullYear()} GlobalBiz Platform. All rights reserved.
                        </p>
                        <p className="text-xs text-gray-500 mt-1">
                            Connecting businesses, jobs, tenders, auctions, and investments worldwide.
                        </p>
                    </div>
                    <nav className="flex items-center gap-4 text-sm">
                        <Link href="/contacts" className="text-gray-600 hover:text-gray-900">Contacts</Link>
                        <Link href="/news" className="text-gray-600 hover:text-gray-900">News</Link>
                        <Link href="/partnership" className="text-gray-600 hover:text-gray-900">Partnership</Link>
                        <Link href="/forum" className="text-gray-600 hover:text-gray-900">Forum</Link>
                    </nav>
                </div>
            </div>
        </footer>
    );
}