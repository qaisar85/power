import React from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';
import { Shield, Settings, LogIn } from 'lucide-react';

export default function AdminIndex({ modules }) {
    return (
        <AppLayout title="Admin Panel">
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-bold text-gray-900">Central Admin Panel</h1>
                            <p className="mt-2 text-gray-600">Manage users, global settings, and portal modules.</p>
                        </div>
                        <div className="flex items-center space-x-2 text-indigo-600">
                            <Shield className="h-5 w-5" />
                            <span className="text-sm">SSO Enabled via Sanctum</span>
                        </div>
                    </div>

                    <div className="bg-white shadow rounded-lg">
                        <div className="p-4 border-b flex items-center justify-between">
                            <div className="flex items-center text-gray-700">
                                <Settings className="h-5 w-5 mr-2" />
                                <span className="font-medium">Portal Modules</span>
                            </div>
                            <div className="text-sm text-gray-500">
                                {modules.length} modules configured
                            </div>
                        </div>
                        <div className="p-4 overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin URL</th>
                                        <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {modules.map((m) => (
                                        <tr key={m.id}>
                                            <td className="px-3 py-2 text-sm text-gray-900">{m.name}</td>
                                            <td className="px-3 py-2 text-sm text-gray-600">{m.slug}</td>
                                            <td className="px-3 py-2 text-sm">
                                                <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-indigo-50 text-indigo-700">
                                                    {m.integration_type}
                                                </span>
                                            </td>
                                            <td className="px-3 py-2 text-sm">
                                                {m.is_active ? (
                                                    <span className="text-green-600">Active</span>
                                                ) : (
                                                    <span className="text-gray-500">Inactive</span>
                                                )}
                                            </td>
                                            <td className="px-3 py-2 text-sm text-gray-600">
                                                {m.admin_url ? (
                                                    <a href={m.admin_url} target="_blank" rel="noopener noreferrer" className="text-indigo-600 hover:text-indigo-700">
                                                        {m.admin_url}
                                                    </a>
                                                ) : (
                                                    <span className="text-gray-400">—</span>
                                                )}
                                            </td>
                                            <td className="px-3 py-2 text-sm">
                                                {m.admin_url ? (
                                                    <Link
                                                        href={`/admin/connect/${m.slug}`}
                                                        className="inline-flex items-center px-3 py-1.5 rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                                    >
                                                        <LogIn className="h-4 w-4 mr-1" /> SSO to Admin
                                                    </Link>
                                                ) : (
                                                    <span className="text-gray-400">No admin configured</span>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}