import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function AccountMessages({ inbox = [], outbox = [] }) {
  const [tab, setTab] = useState('inbox');
  return (
    <AppLayout title="Messages">
      <Head title="Messages" />
      <div className="max-w-5xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Messages</h1>
          <Link href={route('account')} className="text-indigo-600 hover:text-indigo-800">Back to Account</Link>
        </div>

        <div className="mb-4 flex gap-2">
          <button
            type="button"
            onClick={() => setTab('inbox')}
            className={`px-3 py-2 rounded border ${tab === 'inbox' ? 'bg-indigo-600 text-white' : 'bg-gray-50'}`}
          >
            Inbox (requests to your listings)
          </button>
          <button
            type="button"
            onClick={() => setTab('outbox')}
            className={`px-3 py-2 rounded border ${tab === 'outbox' ? 'bg-indigo-600 text-white' : 'bg-gray-50'}`}
          >
            Outbox (your requests)
          </button>
        </div>

        {tab === 'inbox' ? <RequestsList items={inbox} noItemsText="No messages yet to your listings." /> : null}
        {tab === 'outbox' ? <RequestsList items={outbox} noItemsText="You haven't sent any requests yet." /> : null}

        <div className="mt-8 p-4 bg-blue-50 border border-blue-200 rounded">
          <p className="text-sm text-gray-700">
            This section consolidates contact, inspection, shipping, and subscription requests.
            A conversation UI with moderator support will appear here shortly.
          </p>
        </div>
      </div>
    </AppLayout>
  );
}

function RequestsList({ items, noItemsText }) {
  if (!items || items.length === 0) {
    return <p className="text-gray-600">{noItemsText}</p>;
  }
  return (
    <div className="bg-white shadow rounded">
      <table className="min-w-full text-sm">
        <thead>
          <tr className="border-b">
            <th className="text-left p-3">Type</th>
            <th className="text-left p-3">Listing</th>
            <th className="text-left p-3">From</th>
            <th className="text-left p-3">Status</th>
            <th className="text-left p-3">Date</th>
            <th className="text-left p-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          {items.map((r) => (
            <tr key={`req-${r.id}`} className="border-b">
              <td className="p-3 capitalize">{r.type}</td>
              <td className="p-3">
                <Link className="text-indigo-600" href={route('listings.show', r.listing_id)}>
                  {r.listing_title}
                </Link>
              </td>
              <td className="p-3">{r.from_name || r.user_name || '—'}</td>
              <td className="p-3">{r.status}</td>
              <td className="p-3">{r.created_at}</td>
              <td className="p-3">
                <button className="px-3 py-1 text-xs rounded bg-gray-100 border">Open</button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}