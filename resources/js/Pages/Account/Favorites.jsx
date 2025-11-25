import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function FavoritesPage({ favorites = [] }) {
  return (
    <AppLayout>
      <Head title="My Favorites" />
      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          <div className="flex justify-between items-center">
            <h1 className="text-2xl font-semibold text-gray-900">My Favorites</h1>
            <Link href="/dashboard" className="text-sm text-indigo-600 hover:text-indigo-800">Back to Dashboard</Link>
          </div>

          {favorites.length === 0 ? (
            <div className="bg-white shadow sm:rounded-lg p-6">
              <p className="text-sm text-gray-600">You have not added any favorites yet.</p>
              <p className="mt-2 text-sm text-gray-600">Go to a listing and click "Add to Favorites" to save it here.</p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {favorites.map((f) => (
                <div key={f.id} className="bg-white shadow rounded p-4">
                  <div className="flex items-start justify-between">
                    <div>
                      <h3 className="text-lg font-medium text-gray-900">{f.title}</h3>
                      <p className="mt-1 text-sm text-gray-600">
                        {Array.isArray(f.subcategories) && f.subcategories.length > 0
                          ? `${f.category} / ${f.subcategories.join(', ')}`
                          : f.category}
                        {' '}· {f.deal_type} · {f.location}
                      </p>
                    </div>
                    <div className="text-right">
                      <div className="text-lg font-bold text-gray-900">{f.currency} {f.price ?? '—'}</div>
                      <p className="text-xs text-gray-500">Saved: {f.created_at}</p>
                    </div>
                  </div>
                  {Array.isArray(f.photos) && f.photos.length > 0 && (
                    <div className="mt-3 grid grid-cols-2 gap-2">
                      {f.photos.slice(0, 2).map((src, idx) => (
                        <img key={idx} src={src} alt="photo" className="rounded object-cover h-24 w-full" />
                      ))}
                    </div>
                  )}
                  <div className="mt-4 flex justify-between items-center">
                    <Link href={route('listings.show', f.listing_id)} className="px-3 py-1.5 text-sm rounded bg-indigo-600 text-white hover:bg-indigo-700">Open Listing</Link>
                    <Link href={route('listings.favorite.delete', f.listing_id)} as="button" method="delete" className="px-3 py-1.5 text-sm rounded bg-gray-100 border hover:bg-gray-200">Remove</Link>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </AppLayout>
  );
}