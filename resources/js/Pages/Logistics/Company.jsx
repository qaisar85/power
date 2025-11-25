import React from 'react';
import { Head, usePage, Link } from '@inertiajs/react';

export default function LogisticsCompany() {
  const { company, routes, contacts_open } = usePage().props;

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title={`${company?.name || 'Company'} — Logistics`} />
      <div className="max-w-6xl mx-auto py-8 px-4">
        <div className="bg-white rounded shadow p-6 mb-6 flex items-start justify-between">
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-2xl font-semibold">{company?.name}</h1>
              {company?.is_verified && (
                <span className="inline-flex items-center text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Verified</span>
              )}
            </div>
            <div className="text-sm text-gray-600">{company?.address?.country || '—'}</div>
          </div>
          <div className="text-sm text-gray-600">
            <Link href={route('logistics.index')} className="text-blue-600">Back to catalog</Link>
          </div>
        </div>

        {/* Banner / Description */}
        <div className="bg-white rounded shadow p-6 mb-6">
          {company?.banner ? (
            <img src={company.banner.startsWith('http') ? company.banner : `/storage/${company.banner}`} alt={company.name} className="w-full h-48 object-cover rounded mb-4" />
          ) : null}
          <p className="text-gray-700">{company?.description || 'No description available.'}</p>
        </div>

        {/* Routes */}
        <h2 className="text-lg font-semibold mb-3">Routes</h2>
        <div className="bg-white rounded shadow">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Type</th>
                <th className="text-left p-3">From</th>
                <th className="text-left p-3">To</th>
                <th className="text-left p-3">Frequency</th>
                <th className="text-left p-3">Timeline</th>
                <th className="text-left p-3">Tariffs</th>
              </tr>
            </thead>
            <tbody>
              {(routes || []).length ? routes.map((r) => (
                <tr key={`r-${r.id}`} className="border-b">
                  <td className="p-3 capitalize">{r.transport_type}</td>
                  <td className="p-3">{r.from_city ? `${r.from_city}, ` : ''}{r.from_country}</td>
                  <td className="p-3">{r.to_city ? `${r.to_city}, ` : ''}{r.to_country}</td>
                  <td className="p-3">{r.frequency || '—'}</td>
                  <td className="p-3">{r.timeline_days ? `${r.timeline_days} days` : '—'}</td>
                  <td className="p-3">
                    <div className="text-xs text-gray-700 space-y-1">
                      {r.price_per_kg ? (<div>Per kg: <span className="font-medium">{r.price_per_kg} USD</span></div>) : null}
                      {r.price_per_ton ? (<div>Per ton: <span className="font-medium">{r.price_per_ton} USD</span></div>) : null}
                      {r.price_per_container ? (<div>Per container: <span className="font-medium">{r.price_per_container} USD</span></div>) : null}
                      {!r.price_per_kg && !r.price_per_ton && !r.price_per_container ? '—' : null}
                    </div>
                  </td>
                </tr>
              )) : (
                <tr>
                  <td className="p-4 text-center text-gray-500" colSpan={6}>No routes published.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {/* Contact / Request CTA */}
        <div className="mt-6 bg-white rounded shadow p-6">
          {contacts_open ? (
            <div className="flex items-center justify-between">
              <div>
                <div className="text-sm text-gray-600">Contacts are open for this company.</div>
                <div className="text-sm">Email: {company?.email || '—'} | Phone: {company?.phone || '—'}</div>
              </div>
              <Link href={route('service.requests.index')} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Send Request</Link>
            </div>
          ) : (
            <div className="flex items-center justify-between">
              <div className="text-sm text-gray-700">Contacts are hidden under commission model. Your request will be routed via admin.</div>
              <Link href={route('login')} className="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Login to Send Request</Link>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}