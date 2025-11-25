import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function AccountListings({ listings = [] }) {
  return (
    <AppLayout title="My Listings">
      <Head title="My Listings" />
      <div className="max-w-5xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">My Listings</h1>
          <Link href={route('listings.create')} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">+ Create new listing</Link>
        </div>

        {listings.length === 0 ? (
          <div className="bg-white shadow rounded p-6">
            <p className="text-gray-700">You don't have any listings yet.</p>
            <p className="text-sm text-gray-500 mt-1">Click the button above to create one.</p>
          </div>
        ) : (
          <div className="bg-white shadow rounded">
            <table className="min-w-full text-sm">
              <thead>
                <tr className="border-b">
                  <th className="text-left p-3">Title</th>
                  <th className="text-left p-3">Type</th>
                  <th className="text-left p-3">Status</th>
                  <th className="text-left p-3">Price</th>
                  <th className="text-left p-3">Date</th>
                  <th className="text-left p-3">Actions</th>
                </tr>
              </thead>
              <tbody>
                {listings.map((l) => (
                  <tr key={`l-${l.id}`} className="border-b">
                    <td className="p-3">
                      <Link className="text-indigo-600" href={route('listings.show', l.id)}>
                        {l.title}
                      </Link>
                    </td>
                    <td className="p-3">{l.deal_type}</td>
                    <td className="p-3 capitalize">{l.status}</td>
                    <td className="p-3">{l.price ? `${l.price} ${l.currency || ''}` : '—'}</td>
                    <td className="p-3">{l.created_at}</td>
                    <td className="p-3">
                      <button className="px-3 py-1 text-xs rounded bg-gray-100 border">Edit</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </AppLayout>
  );
}