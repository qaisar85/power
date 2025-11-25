import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function FinancesIndex({ company, plans }) {
  return (
    <AppLayout title="Finances & Subscription">
      <Head title="Finances & Subscription" />
      <div className="max-w-6xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-bold">Finances & Subscription</h1>
            <p className="text-gray-600 text-sm">Current plan: {company.plan?.name || '—'}; Expires: {company.plan_expires_at || '—'}</p>
          </div>
          <Link href={route('service.dashboard')} className="text-indigo-600 hover:text-indigo-800">Back to dashboard</Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {plans.map((p) => (
            <div key={`p-${p.id}`} className="bg-white shadow rounded p-6">
              <h3 className="text-lg font-medium">{p.name}</h3>
              <p className="text-sm text-gray-600">Price: ${p.price?.toFixed(2) ?? 'N/A'} / {p.duration_days} days</p>
              <p className="text-sm text-gray-600 mt-1">Visibility: {p.visibility_type}</p>
              <p className="text-sm text-gray-600">Service limit: {p.service_limit ?? '∞'}</p>
              <div className="mt-4">
                <Link
                  href={route('service.finances.plan')}
                  method="post"
                  data={{ plan_id: p.id }}
                  as="button"
                  className="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm"
                >
                  Choose {p.name}
                </Link>
              </div>
            </div>
          ))}
        </div>
      </div>
    </AppLayout>
  );
}