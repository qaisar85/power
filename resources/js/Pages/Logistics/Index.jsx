import React, { useMemo, useState } from 'react';
import { Head, router, usePage, Link } from '@inertiajs/react';

export default function LogisticsIndex() {
  const { companies, filters } = usePage().props;
  const [localFilters, setLocalFilters] = useState({
    type: filters?.type || '',
    from_country: filters?.from_country || '',
    to_country: filters?.to_country || '',
    cert: filters?.cert || '',
  });

  const onChange = (e) => {
    const { name, value } = e.target;
    setLocalFilters((f) => ({ ...f, [name]: value }));
  };

  const submitFilters = (e) => {
    e.preventDefault();
    const params = new URLSearchParams();
    Object.entries(localFilters).forEach(([k, v]) => { if (v) params.set(k, v); });
    router.get('/logistics' + (params.toString() ? `?${params.toString()}` : ''), {}, { preserveState: true, preserveScroll: true });
  };

  const clearFilters = () => {
    setLocalFilters({ type: '', from_country: '', to_country: '', cert: '' });
    router.get('/logistics', {}, { preserveState: true, preserveScroll: true });
  };

  const items = useMemo(() => companies?.data || [], [companies]);

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title="Logistics Companies" />
      <div className="max-w-7xl mx-auto py-8 px-4">
        <h1 className="text-2xl font-semibold mb-4">Logistics Companies</h1>

        {/* Filters */}
        <form onSubmit={submitFilters} className="bg-white shadow rounded p-4 mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Transport Type</label>
            <select name="type" value={localFilters.type} onChange={onChange} className="mt-1 block w-full border rounded px-3 py-2">
              <option value="">Any</option>
              <option value="road">Road</option>
              <option value="air">Air</option>
              <option value="sea">Sea</option>
              <option value="rail">Rail</option>
              <option value="warehousing">Warehousing</option>
              <option value="customs">Customs</option>
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">From Country</label>
            <input name="from_country" value={localFilters.from_country} onChange={onChange} className="mt-1 block w-full border rounded px-3 py-2" placeholder="e.g. UAE" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">To Country</label>
            <input name="to_country" value={localFilters.to_country} onChange={onChange} className="mt-1 block w-full border rounded px-3 py-2" placeholder="e.g. Saudi Arabia" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Certification</label>
            <select name="cert" value={localFilters.cert} onChange={onChange} className="mt-1 block w-full border rounded px-3 py-2">
              <option value="">Any</option>
              <option value="IATA">IATA</option>
              <option value="FIATA">FIATA</option>
              <option value="ISO">ISO</option>
            </select>
          </div>
          <div className="flex items-end gap-2">
            <button type="submit" className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Apply</button>
            <button type="button" onClick={clearFilters} className="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200">Clear</button>
          </div>
        </form>

        {/* Companies grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {items.map((c) => (
            <div key={c.id} className="bg-white rounded shadow overflow-hidden">
              {c.logo ? (
                <img src={c.logo.startsWith('http') ? c.logo : `/storage/${c.logo}`} alt={c.name} className="w-full h-36 object-contain p-4" />
              ) : (
                <div className="w-full h-36 bg-gray-100 flex items-center justify-center text-gray-400">No Logo</div>
              )}
              <div className="p-4">
                <div className="flex items-center gap-2">
                  <h3 className="font-semibold text-lg">{c.name}</h3>
                  {c.is_verified && (
                    <span className="inline-flex items-center text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Verified</span>
                  )}
                </div>
                <p className="text-sm text-gray-600 line-clamp-3">{c.description || '—'}</p>
                <div className="mt-2 text-sm">
                  <span className="text-gray-500">Tariff:</span>{' '}
                  <span className="font-medium">{(c.services || []).find(s => s.category === 'logistics')?.price_value ? `${(c.services || []).find(s => s.category === 'logistics')?.price_value} ${(c.services || []).find(s => s.category === 'logistics')?.currency || 'USD'}` : '—'}</span>
                </div>
                <div className="mt-3 flex items-center justify-between">
                  <Link href={route('logistics.company.show', c.id)} className="text-indigo-600 hover:underline text-sm">View Profile</Link>
                  <a href="#" className="text-blue-600 hover:underline text-sm">Add to Request List</a>
                </div>
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