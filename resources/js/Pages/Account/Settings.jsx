import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function AccountSettings({ user = {}, profileUrl = '/user/profile', billingUrl = null }) {
  return (
    <AppLayout title="Settings">
      <Head title="Settings" />
      <div className="max-w-4xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Account Settings</h1>
          <Link href={route('account')} className="text-indigo-600 hover:text-indigo-800">Back to Account</Link>
        </div>

        <div className="bg-white shadow rounded p-6">
          <h2 className="text-lg font-medium mb-2">Profile & Companies</h2>
          <p className="text-gray-700 mb-3">Manage your personal details and company profiles.</p>
          <div className="flex gap-3">
            <Link href={profileUrl} className="px-3 py-2 rounded bg-gray-100 border hover:bg-gray-200 text-sm">Open Profile</Link>
            <Link href="/dashboard" className="px-3 py-2 rounded bg-gray-100 border hover:bg-gray-200 text-sm">Open Companies</Link>
          </div>
        </div>

        <div className="bg-white shadow rounded p-6 mt-6">
          <h2 className="text-lg font-medium mb-2">Billing & Subscriptions</h2>
          <p className="text-gray-700 mb-3">View packages, limits, and payment history.</p>
          <Link href={billingUrl || route('packages.index')} className="px-3 py-2 rounded bg-gray-100 border hover:bg-gray-200 text-sm">Manage Subscriptions</Link>
        </div>
      </div>
    </AppLayout>
  );
}