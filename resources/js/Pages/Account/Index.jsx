import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function AccountIndex({ user = {}, stats = {} }) {
  return (
    <AppLayout title="My Account">
      <Head title="My Account" />

      <div className="max-w-5xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-bold">Welcome, {user.name || 'User'}</h1>
            <p className="text-gray-600">Manage your listings, messages, subscriptions, and settings.</p>
          </div>
          <Link
            href={route('listings.create')}
            className="inline-flex items-center px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700"
          >
            + Create new listing
          </Link>
        </div>

        {/* Quick stats */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <StatCard label="My listings" value={stats.listingsCount ?? 0} />
          <StatCard label="Favorites" value={stats.favoritesCount ?? 0} />
          <StatCard label="Subscriptions" value={stats.subscriptionsCount ?? 0} />
          <StatCard label="Messages" value={stats.messagesCount ?? 0} />
        </div>

        {/* Sections */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <SectionCard
            title="My listings"
            description="View and manage your equipment listings."
            actionLabel="Open"
            href={route('account.listings')}
          />
          <SectionCard
            title="Messages"
            description="Chat with buyers and moderators (coming soon)."
            actionLabel="Open"
            href={route('account.messages')}
          />
          <SectionCard
            title="Subscriptions"
            description="Packages, limits, and billing history."
            actionLabel="Manage"
            href={route('packages.index')}
          />
          <SectionCard
            title="Settings"
            description="Profile, contacts, companies, and preferences."
            actionLabel="Manage"
            href={route('account.settings')}
          />
        </div>

        {/* Helper */}
        <div className="mt-8 p-4 bg-blue-50 border border-blue-200 rounded">
          <p className="text-sm text-gray-700">
            To create an equipment listing for sale, rent, auction, or wanted, click the
            <span className="font-semibold"> “Create new listing” </span> button above. In the wizard, select
            the required <span className="font-semibold">Listing type</span> and fill in details.
          </p>
        </div>
      </div>
    </AppLayout>
  );
}

function StatCard({ label, value }) {
  return (
    <div className="bg-white shadow rounded p-4 text-center">
      <div className="text-2xl font-bold">{value}</div>
      <div className="text-sm text-gray-600">{label}</div>
    </div>
  );
}

function SectionCard({ title, description, actionLabel, href }) {
  return (
    <div className="bg-white shadow rounded p-4 flex flex-col">
      <h2 className="text-lg font-medium mb-1">{title}</h2>
      <p className="text-sm text-gray-600 mb-3">{description}</p>
      <div className="mt-auto">
        <Link href={href} className="inline-flex items-center px-3 py-2 rounded bg-gray-100 border hover:bg-gray-200 text-sm">
          {actionLabel}
        </Link>
      </div>
    </div>
  );
}