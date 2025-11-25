import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Index() {
    const { props } = usePage();
    const { holding, transactions, kyc_status, disclaimer, notifications = [] } = props;

    return (
        <AppLayout title="Investor Dashboard">
            <Head title="Investor Dashboard" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Left column: KYC + Holdings */}
                        <div className="col-span-2 space-y-6">
                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h2 className="text-lg font-semibold mb-2">KYC Status</h2>
                                <p className="text-sm">{kyc_status === 'approved' ? 'Your identity is verified.' : kyc_status === 'pending' ? 'Your verification is in review.' : 'You have not completed KYC yet.'}</p>
                                <div className="mt-3">
                                    <Link href="/kyc" className="text-indigo-600 hover:text-indigo-800">Manage KYC</Link>
                                </div>
                            </div>

                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h2 className="text-lg font-semibold mb-2">Current Holdings</h2>
                                <p className="text-sm">Total shares: <span className="font-medium">{holding?.shares ?? 0}</span></p>
                                <div className="mt-4 flex gap-3">
                                    <Link href="/stocks/buy" className="px-4 py-2 bg-indigo-600 text-white rounded">Buy Shares</Link>
                                    <Link href="/stocks/market" className="px-4 py-2 bg-gray-100 text-gray-800 rounded">Market</Link>
                                </div>
                            </div>

                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h2 className="text-lg font-semibold mb-4">Recent Transactions</h2>
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shares</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {(transactions || []).map((t) => (
                                                <tr key={t.id}>
                                                    <td className="px-4 py-2 text-sm text-gray-700">{t.id}</td>
                                                    <td className="px-4 py-2 text-sm text-gray-700">{t.type}</td>
                                                    <td className="px-4 py-2 text-sm text-gray-700">{t.shares}</td>
                                                    <td className="px-4 py-2 text-sm text-gray-700">{t.amount}</td>
                                                    <td className="px-4 py-2 text-sm text-gray-700">{t.status}</td>
                                                    <td className="px-4 py-2 text-sm text-gray-700">{t.created_at}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {/* Right column: Notifications */}
                        <div className="col-span-1 space-y-6">
                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h2 className="text-lg font-semibold mb-2">Notifications</h2>
                                <div className="flex items-center justify-between mb-2">
                                    <span className="text-sm text-gray-600">Latest updates</span>
                                    {notifications.length > 0 && (
                                        <Link href={route('notifications.read_all')} method="post" as="button" className="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700">Mark all as read</Link>
                                    )}
                                </div>
                                {notifications.length === 0 ? (
                                    <p className="text-sm text-gray-600">No recent notifications.</p>
                                ) : (
                                    <ul className="divide-y divide-gray-200">
                                        {notifications.map((n) => (
                                            <li key={n.id} className="py-3">
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <p className="text-sm font-medium text-gray-900">{n.data?.title || n.type}</p>
                                                        <p className="text-sm text-gray-600">{n.data?.message || n.data?.title || 'Notification'}</p>
                                                        {n.data?.shares && (
                                                            <p className="text-xs text-gray-500">Shares: {n.data.shares} — Amount: {n.data.amount}</p>
                                                        )}
                                                        <div className="mt-2 flex items-center gap-3">
                                                            {!n.read_at && (
                                                                <Link href={`/notifications/${n.id}/read`} method="post" as="button" className="text-xs text-gray-600 hover:text-gray-800">Mark as read</Link>
                                                            )}
                                                            {n.data?.cta_url && (
                                                                <Link href={n.data.cta_url} className="text-xs text-indigo-600 hover:text-indigo-800">{n.data?.cta_label || 'Open'}</Link>
                                                            )}
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <span className={`text-xs ${n.read_at ? 'text-gray-400' : 'text-green-600'}`}>{n.read_at ? 'Read' : 'New'}</span>
                                                        <div className="text-xs text-gray-500">{n.created_at}</div>
                                                    </div>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>

                            <div className="bg-white shadow sm:rounded-lg p-6">
                                <h2 className="text-lg font-semibold mb-2">Disclaimer</h2>
                                <p className="text-sm text-gray-600">{disclaimer}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}