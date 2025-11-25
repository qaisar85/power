import React from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ActivityIndex({ company, activities = [], filters = {} }) {
  const quickActions = [
    'case_created',
    'case_updated',
    'case_deleted',
    'review_reported',
    'review_replied',
    'review_submitted',
    'document_uploaded',
    'document_verified',
    'document_rejected',
  ];

  const presets = {
    'Case lifecycle': ['case_created', 'case_updated', 'case_deleted'],
    'Reviews': ['review_reported', 'review_replied', 'review_submitted'],
    'Documents': ['document_uploaded', 'document_verified', 'document_rejected'],
  };

  const [form, setForm] = React.useState({
    actions: filters.actions || [],
    action: filters.action || '', // optional custom single action
    from: filters.from || '',
    to: filters.to || '',
    actor: filters.actor || '',
  });

  const [showFullContext, setShowFullContext] = React.useState(false);

  const submit = (e) => {
    e.preventDefault();
    router.get(route('service.activity.index'), form, { preserveState: true, replace: true });
  };

  const onSelectActions = (e) => {
    const selected = Array.from(e.target.selectedOptions).map(o => o.value);
    setForm({ ...form, actions: selected });
  };

  const addPreset = (key) => {
    const next = Array.from(new Set([...(form.actions || []), ...presets[key]]));
    setForm({ ...form, actions: next });
  };

  const clearActions = () => setForm({ ...form, actions: [] });

  const formatContext = (ctx) => {
    if (!ctx) return '';
    const keys = Object.keys(ctx);
    if (!keys.length) return '';
    const preferred = [];
    if (ctx.case_id !== undefined) preferred.push(`case_id=${ctx.case_id}`);
    if (ctx.service_id !== undefined) preferred.push(`service_id=${ctx.service_id}`);
    const others = keys
      .filter(k => k !== 'case_id' && k !== 'service_id')
      .map(k => `${k}=${Array.isArray(ctx[k]) ? JSON.stringify(ctx[k]) : ctx[k]}`);
    return showFullContext ? [...preferred, ...others].join(' · ') : preferred.join(' · ');
  };

  return (
    <AppLayout title="Activity History">
      <Head title="Activity History" />
      <div className="max-w-6xl mx-auto py-6 px-4">
        <h1 className="text-2xl font-bold mb-6">Activity History</h1>

        <form onSubmit={submit} className="mb-4 bg-white shadow rounded p-4 flex flex-wrap gap-3 items-end">
          <div>
            <label className="block text-sm font-medium">Actions</label>
            <select
              multiple
              className="mt-1 border rounded p-2 w-56 h-32"
              value={form.actions}
              onChange={onSelectActions}
            >
              {quickActions.map((a) => (
                <option key={a} value={a}>{a}</option>
              ))}
            </select>
            <p className="mt-1 text-xs text-gray-500">Use Ctrl/Command to select multiple.</p>
          </div>
          <div>
            <label className="block text-sm font-medium">Action (custom)</label>
            <input
              className="mt-1 border rounded p-2 w-56"
              placeholder="e.g. case_created"
              value={form.action}
              onChange={(e) => setForm({ ...form, action: e.target.value })}
            />
          </div>
          <div>
            <label className="block text-sm font-medium">Actor</label>
            <input
              className="mt-1 border rounded p-2 w-56"
              placeholder="e.g. Alice"
              value={form.actor}
              onChange={(e) => setForm({ ...form, actor: e.target.value })}
            />
          </div>
          <div>
            <label className="block text-sm font-medium">From</label>
            <input
              type="date"
              className="mt-1 border rounded p-2"
              value={form.from}
              onChange={(e) => setForm({ ...form, from: e.target.value })}
            />
          </div>
          <div>
            <label className="block text-sm font-medium">To</label>
            <input
              type="date"
              className="mt-1 border rounded p-2"
              value={form.to}
              onChange={(e) => setForm({ ...form, to: e.target.value })}
            />
          </div>
          <div className="flex items-center gap-2">
            <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded">Apply Filters</button>
            <button type="button" onClick={clearActions} className="px-3 py-2 bg-gray-100 border rounded">Clear actions</button>
          </div>

          <div className="w-full flex flex-wrap gap-2">
            {Object.keys(presets).map((key) => (
              <button
                type="button"
                key={key}
                onClick={() => addPreset(key)}
                className="px-2 py-1 text-xs bg-indigo-50 border border-indigo-200 text-indigo-700 rounded"
              >
                Add {key}
              </button>
            ))}
          </div>
        </form>

        <div className="flex items-center justify-end mb-2">
          <label className="flex items-center gap-2 text-sm">
            <input type="checkbox" checked={showFullContext} onChange={(e)=>setShowFullContext(e.target.checked)} />
            Show full context
          </label>
        </div>

        <div className="bg-white shadow rounded">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Date</th>
                <th className="text-left p-3">Actor</th>
                <th className="text-left p-3">Action</th>
                <th className="text-left p-3">Context</th>
              </tr>
            </thead>
            <tbody>
              {activities.length ? activities.map((a, idx) => (
                <tr key={`a-${idx}`} className="border-b">
                  <td className="p-3">{a.date}</td>
                  <td className="p-3">{a.actor}</td>
                  <td className="p-3">{a.action}</td>
                  <td className="p-3 text-gray-700">{formatContext(a.context)}</td>
                </tr>
              )) : (
                <tr>
                  <td colSpan="4" className="p-4 text-center text-gray-600">No activity yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}