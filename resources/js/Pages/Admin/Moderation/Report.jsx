import React from 'react';
import { Link, useForm } from '@inertiajs/react';

export default function ModerationReport({ filters, statusCounts, priorityCounts, daily }) {
  const { data, setData, get } = useForm({
    item_type: filters?.item_type || '',
    from: filters?.from || '',
    to: filters?.to || '',
  });

  const submit = (e) => {
    e.preventDefault();
    get(route('admin.moderation.report'), { preserveScroll: true, preserveState: true, data });
  };

  const itemTypes = [
    '', 'company_doc', 'product_card', 'tender', 'auction', 'inspection_request', 'logistics', 'complaint', 'spam', 'user', 'news'
  ];

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Moderation Report</h1>
        <Link href={route('admin.moderation.index')} className="text-indigo-600">Back to Moderation</Link>
      </div>

      {/* Filters */}
      <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
          <label className="block text-sm">Item type</label>
          <select
            value={data.item_type}
            onChange={(e) => setData('item_type', e.target.value)}
            className="border rounded px-3 py-2 w-full"
          >
            {itemTypes.map((t) => (
              <option key={t} value={t}>{t || 'All'}</option>
            ))}
          </select>
        </div>
        <div>
          <label className="block text-sm">From</label>
          <input
            type="date"
            value={data.from}
            onChange={(e) => setData('from', e.target.value)}
            className="border rounded px-3 py-2 w-full"
          />
        </div>
        <div>
          <label className="block text-sm">To</label>
          <input
            type="date"
            value={data.to}
            onChange={(e) => setData('to', e.target.value)}
            className="border rounded px-3 py-2 w-full"
          />
        </div>
        <div>
          <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded">Apply</button>
        </div>
      </form>

      {/* Status counts */}
      <div>
        <h2 className="text-lg font-semibold mb-2">By status</h2>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
          {Object.entries(statusCounts || {}).map(([status, total]) => (
            <div key={status} className="border rounded p-3">
              <div className="text-xs text-gray-500">{status}</div>
              <div className="text-2xl font-bold">{total}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Priority counts */}
      <div>
        <h2 className="text-lg font-semibold mb-2">By priority</h2>
        <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
          {Object.entries(priorityCounts || {}).map(([priority, total]) => (
            <div key={priority} className="border rounded p-3">
              <div className="text-xs text-gray-500">Priority {priority}</div>
              <div className="text-2xl font-bold">{total}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Daily counts */}
      <div>
        <h2 className="text-lg font-semibold mb-2">Daily</h2>
        <table className="min-w-full border">
          <thead>
            <tr className="bg-gray-50">
              <th className="p-2 text-left">Day</th>
              <th className="p-2 text-left">Total</th>
            </tr>
          </thead>
          <tbody>
            {daily?.map((row, idx) => (
              <tr key={idx} className="border-t">
                <td className="p-2">{row.day}</td>
                <td className="p-2">{row.total}</td>
              </tr>
            ))}
            {!daily?.length && (
              <tr>
                <td className="p-3" colSpan={2}>No data</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}