import React, { useMemo, useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';

export default function Index() {
  const { companies, filters } = usePage().props;
  const [localFilters, setLocalFilters] = useState({
    type: filters?.type || '',
    method: filters?.method || '',
    region: filters?.region || '',
    depth: filters?.depth || '',
    certificates: Array.isArray(filters?.certificates) ? filters.certificates.join(',') : (filters?.certificates || ''),
  });

  const onChange = (e) => {
    const { name, value } = e.target;
    setLocalFilters((prev) => ({ ...prev, [name]: value }));
  };

  const applyFilters = () => {
    const params = { ...localFilters };
    if (params.certificates) {
      params.certificates = params.certificates.split(',').map((c) => c.trim()).filter(Boolean);
    }
    router.get('/drilling', params, { preserveState: true, preserveScroll: true });
  };

  const clearFilters = () => {
    setLocalFilters({ type: '', method: '', region: '', depth: '', certificates: '' });
    router.get('/drilling', {}, { preserveState: true, preserveScroll: true });
  };

  const items = useMemo(() => companies?.data || [], [companies]);

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title="Drilling Companies" />
      <div className="max-w-7xl mx-auto py-8 px-4">
        <h1 className="text-2xl font-semibold mb-4">Drilling Service — Companies Catalog</h1>

        {/* Filters */}
        <div className="bg-white shadow rounded p-4 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700">Type</label>
              <input name="type" value={localFilters.type} onChange={onChange} className="mt-1 block w-full border rounded px-3 py-2" placeholder="e.g. Onshore / Offshore" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Method</label>
              <input name="method" value={localFilters.method} onChange={onChange} className="mt-1 block w-full border rounded px-3 py-2" placeholder="e.g. Rotary, Directional" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Region</label>
              <input name="region" value={localFilters.region} onChange={onChange} className="mt-1 block w-full border rounded px-3 py-2" placeholder="e.g. MENA, North America" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700">Min Depth (m)</label>
              <input name="depth" value={localFilters.depth} onChange={onChange} type="number" min="0" className="mt-1 block w-full border rounded px-3 py-2" placeholder="e.g. 3000" />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700">Certificates (comma-separated)</label>
              <input name="certificates" value={localFilters.certificates} onChange={onChange} className="mt-1 block w-full border rounded px-3 py-2" placeholder="e.g. ISO9001, API Spec Q1" />
            </div>
          </div>
          <div className="mt-4 flex gap-3">
            <button onClick={applyFilters} className="bg-blue-600 text-white px-4 py-2 rounded">Apply Filters</button>
            <button onClick={clearFilters} className="bg-gray-200 text-gray-900 px-4 py-2 rounded">Clear</button>
          </div>
        </div>

        {/* Companies list */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {items.map((c) => (
            <div key={c.id} className="bg-white shadow rounded p-4">
              <div className="flex items-center gap-3">
                {c.logo ? (
                  <img src={c.logo} alt={c.name} className="w-12 h-12 rounded object-cover" />
                ) : (
                  <div className="w-12 h-12 rounded bg-gray-200 flex items-center justify-center text-gray-500">{c.name?.[0] ?? '?'}</div>
                )}
                <div>
                  <div className="font-semibold">{c.name}</div>
                  <div className="text-sm text-gray-600">{c.region || '—'}</div>
                </div>
                {c.verified && (<span className="ml-auto text-xs bg-green-100 text-green-700 px-2 py-1 rounded">Verified</span>)}
              </div>
              <div className="mt-3 text-sm text-gray-700 line-clamp-3">{c.history || 'No company description provided.'}</div>

              <div className="mt-4">
                <div className="text-sm font-medium text-gray-700 mb-1">Services</div>
                {(c.services || []).slice(0, 3).map((s) => (
                  <div key={s.id} className="text-sm text-gray-600 flex justify-between">
                    <span>{s.type} {s.method ? `— ${s.method}` : ''}</span>
                    <span>{s.depth ? `${s.depth} m` : ''}</span>
                  </div>
                ))}
                {(c.services || []).length === 0 && (
                  <div className="text-sm text-gray-500">No services listed yet.</div>
                )}
              </div>

              <div className="mt-4 flex gap-2">
                <a href={`/companies/${c.id}`} className="text-blue-600 hover:underline text-sm">View card</a>
                <a href={`/companies/${c.id}/request`} className="text-blue-600 hover:underline text-sm">Send request</a>
              </div>
            </div>
          ))}
        </div>

        {/* Pagination */}
        <div className="mt-8 flex justify-center">
          <div className="inline-flex gap-2">
            {companies.prev_page_url && (
              <a href={companies.prev_page_url} className="px-3 py-1 border rounded">Prev</a>
            )}
            <span className="px-3 py-1">Page {companies.current_page} / {companies.last_page}</span>
            {companies.next_page_url && (
              <a href={companies.next_page_url} className="px-3 py-1 border rounded">Next</a>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}