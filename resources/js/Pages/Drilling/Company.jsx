import React, { useMemo, useState } from 'react';
import { Head, usePage, Link } from '@inertiajs/react';

export default function DrillingCompany() {
  const { company, cases } = usePage().props;

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title={`${company?.name || 'Company'} — Drilling`} />
      <div className="max-w-6xl mx-auto py-8 px-4">
        <div className="bg-white rounded shadow p-6 mb-6 flex items-start justify-between">
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-2xl font-semibold">{company?.name}</h1>
              {company?.verified && (
                <span className="inline-flex items-center text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Verified</span>
              )}
            </div>
            <div className="text-sm text-gray-600">{company?.region || '—'}</div>
          </div>
          <div className="text-sm text-gray-600">
            <Link href={route('drilling.index')} className="text-blue-600">Back to catalog</Link>
          </div>
        </div>

        <h2 className="text-lg font-semibold mb-3">Recent Cases</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {(cases || []).map((c) => (
            <div key={c.id} className="bg-white rounded shadow overflow-hidden">
              {Array.isArray(c.photos) && c.photos[0] ? (
                <img src={`/storage/${c.photos[0]}`} alt={c.title} className="w-full h-40 object-cover" />
              ) : (
                <div className="w-full h-40 bg-gray-200 flex items-center justify-center text-gray-500">{c.title?.[0] ?? '?'}</div>
              )}
              <div className="p-4">
                <div className="font-semibold mb-1 flex items-center gap-2">
                  <span>{c.title}</span>
                  {c.status && <span className="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">{c.status}</span>}
                </div>
                <div className="text-sm text-gray-600">{c.client || '—'} • {c.region || '—'}</div>
                <div className="text-xs text-gray-500">{c.method || '—'} • {c.depth ? `${c.depth} m` : ''}</div>
                <div className="text-xs text-gray-500">{c.start_date || '—'} → {c.end_date || '—'}</div>
                {Array.isArray(c.tags) && c.tags.length > 0 && (
                  <div className="mt-1 text-xs text-gray-700">Tags: {c.tags.join(', ')}</div>
                )}
              </div>
            </div>
          ))}
          {(cases || []).length === 0 && (
            <div className="text-sm text-gray-600">No cases published yet.</div>
          )}
        </div>
      </div>
    </div>
  );
}

// Combined component - using the first implementation as the main one
// The second implementation is merged into the first one above