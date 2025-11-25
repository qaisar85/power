import React, { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';

export default function TendersIndex() {
  const { props } = usePage();
  const tenders = props.tenders?.data || [];
  const filters = props.filters || {};
  const [localFilters, setLocalFilters] = useState({
    country: filters.country || '',
    category: filters.category || '',
    sort: filters.sort || 'date',
  });

  const buildQuery = () => {
    const params = new URLSearchParams();
    Object.entries(localFilters).forEach(([k, v]) => {
      if (v) params.append(k, v);
    });
    return params.toString();
  };

  return (
    <div className="container mx-auto p-6">
      <Head title="Tenders" />
      <h1 className="text-2xl font-semibold mb-4">Tenders</h1>

      <div className="bg-white shadow rounded p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input
          className="border rounded px-3 py-2"
          placeholder="Country"
          value={localFilters.country}
          onChange={(e) => setLocalFilters({ ...localFilters, country: e.target.value })}
        />
        <input
          className="border rounded px-3 py-2"
          placeholder="Category"
          value={localFilters.category}
          onChange={(e) => setLocalFilters({ ...localFilters, category: e.target.value })}
        />
        <select
          className="border rounded px-3 py-2"
          value={localFilters.sort}
          onChange={(e) => setLocalFilters({ ...localFilters, sort: e.target.value })}
        >
          <option value="date">Sort by date</option>
          <option value="budget">Sort by budget</option>
          <option value="deadline">Sort by deadline</option>
        </select>
        <Link
          href={`/tenders?${buildQuery()}`}
          className="bg-blue-600 text-white rounded px-4 py-2 text-center"
        >
          Apply Filters
        </Link>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {tenders.map((t) => (
          <div key={t.id} className="border rounded bg-white shadow p-4">
            <h3 className="font-semibold text-lg">{t.title}</h3>
            <p className="text-sm text-gray-600 mb-2">{t.category || '—'} • {t.country || t.location || 'Global'}</p>
            <p className="text-gray-700 line-clamp-3 mb-3">{t.description?.slice(0, 160) || ''}...</p>
            <div className="flex gap-2">
              <Link href={`/tenders/${t.id}`} className="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200">View More</Link>
              <Link href={`/tenders/${t.id}`} className="px-3 py-2 bg-blue-600 text-white rounded">Apply</Link>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}