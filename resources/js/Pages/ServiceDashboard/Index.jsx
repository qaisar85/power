import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PlusCircle, CheckCircle, AlertCircle, Star, FileText, Briefcase, Inbox, ClipboardList, BadgeCheck } from 'lucide-react';

export default function ServiceDashboardIndex({ company, metrics, subscription, can_publish, needs_company }) {
  const tabs = [
    { name: 'Overview', href: route('service.dashboard') },
    { name: 'My Services', href: route('service.services.index') },
    { name: 'Incoming Requests', href: route('service.requests.index') },
    { name: 'Cases & Projects', href: route('service.cases.index') },
    { name: 'Certificates & Documents', href: route('service.documents.index') },
    { name: 'Company Profile', href: route('service.profile.show') },
    { name: 'Finances & Subscription', href: route('service.finances.show') },
    { name: 'Reviews', href: route('service.reviews.index') },
    { name: 'Activity History', href: route('service.activity.index') },
  ];

  return (
    <AppLayout title="Service Company Dashboard">
      <Head title="Service Company Dashboard" />
      <div className="max-w-7xl mx-auto py-6 px-4">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-bold">{company?.name || 'Your Company'}</h1>
            <p className="text-gray-600">Manage services, requests, cases, documents, and reputation.</p>
          </div>
          <Link href={route('service.services.create')} className="inline-flex items-center px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
            <PlusCircle className="h-5 w-5 mr-2" /> Add Service
          </Link>
        </div>

        {/* Widgets */}
        <div className="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
          <Widget icon={Briefcase} label="Active services" value={metrics?.active_services ?? 0} />
          <Widget icon={Inbox} label="New requests" value={metrics?.new_requests ?? 0} />
          <Widget icon={ClipboardList} label="Completed cases" value={metrics?.completed_cases ?? 0} />
          <Widget icon={Star} label="Rating" value={metrics?.rating ?? '-'} />
        </div>

        {/* Subscription status */}
        <div className="bg-white shadow rounded p-4 mb-6">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-lg font-medium">Subscription</h2>
              <p className="text-sm text-gray-600">Plan: {subscription?.plan || '—'}; Expires: {subscription?.expires_at || '—'}</p>
              <p className="text-sm">Verification: {subscription?.is_verified ? 'Verified' : 'Not verified'}</p>
            </div>
            <div className="flex gap-3">
              <Link href={route('service.finances.show')} className="px-3 py-2 rounded bg-gray-100 border hover:bg-gray-200 text-sm">Upgrade tariff</Link>
              {can_publish ? (
                <span className="inline-flex items-center text-green-700"><CheckCircle className="h-4 w-4 mr-1" /> Can publish</span>
              ) : (
                <span className="inline-flex items-center text-yellow-700"><AlertCircle className="h-4 w-4 mr-1" /> Publishing limited until verification</span>
              )}
            </div>
          </div>
        </div>

        {/* Tabs navigation */}
        <div className="bg-white shadow rounded p-4">
          <div className="flex flex-wrap gap-3 mb-4">
            {tabs.map((t) => (
              <Link key={t.name} href={t.href} className="px-3 py-2 rounded bg-gray-100 border hover:bg-gray-200 text-sm">
                {t.name}
              </Link>
            ))}
          </div>

          <div className="text-gray-700 text-sm">
            {needs_company ? (
              <div className="flex items-center justify-between">
                <p>No company profile found. Connect a company to use the Service Dashboard.</p>
                <Link href={route('service.profile.show')} className="inline-flex items-center px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Connect Company</Link>
              </div>
            ) : (
              <p>Use the tabs above to manage your services and operations.</p>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

function Widget({ icon: Icon, label, value }) {
  return (
    <div className="bg-white shadow rounded p-4">
      <div className="flex items-center gap-3">
        <div className="h-10 w-10 flex items-center justify-center rounded bg-indigo-50 text-indigo-600">
          <Icon className="h-6 w-6" />
        </div>
        <div>
          <div className="text-sm text-gray-500">{label}</div>
          <div className="text-xl font-semibold">{value}</div>
        </div>
      </div>
    </div>
  );
}