import React, { useState } from 'react';
import { Head, usePage, useForm, router } from '@inertiajs/react';

export default function HseIndex() {
  const { company, documents } = usePage().props;
  const [adding, setAdding] = useState(false);

  const form = useForm({
    title: '',
    type: '',
    issued_at: '',
    expires_at: '',
    region: '',
    description: '',
    file: null,
  });

  const submit = (e) => {
    e.preventDefault();
    form.post(route('service.drilling.hse.store'), {
      forceFormData: true,
      onSuccess: () => {
        setAdding(false);
        form.reset();
      },
    });
  };

  const expired = (d) => {
    if (!d.expires_at) return false;
    const exp = new Date(d.expires_at);
    return exp < new Date();
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title={`HSE — ${company?.name || 'Drilling'}`} />
      <div className="max-w-5xl mx-auto py-8 px-4">
        <div className="flex items-center justify-between mb-4">
          <h1 className="text-xl font-semibold">HSE Documents</h1>
          <button onClick={() => setAdding((v) => !v)} className="bg-blue-600 text-white px-3 py-2 rounded text-sm">
            {adding ? 'Close' : 'Add Document'}
          </button>
        </div>

        {adding && (
          <form onSubmit={submit} className="bg-white rounded shadow p-4 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Title</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} required />
            </div>
            <div>
              <label className="block text-sm font-medium">Type</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.type} onChange={(e) => form.setData('type', e.target.value)} required />
            </div>
            <div>
              <label className="block text-sm font-medium">Issued at</label>
              <input type="date" className="mt-1 w-full border rounded px-3 py-2" value={form.data.issued_at} onChange={(e) => form.setData('issued_at', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Expires at</label>
              <input type="date" className="mt-1 w-full border rounded px-3 py-2" value={form.data.expires_at} onChange={(e) => form.setData('expires_at', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Region</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.region} onChange={(e) => form.setData('region', e.target.value)} />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium">Description</label>
              <textarea className="mt-1 w-full border rounded px-3 py-2" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium">File (PDF or image)</label>
              <input type="file" accept="application/pdf,image/*" onChange={(e) => form.setData('file', e.target.files[0])} />
            </div>
            <div className="md:col-span-2">
              <button type="submit" className="bg-green-600 text-white px-4 py-2 rounded">Save Document</button>
            </div>
          </form>
        )}

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {(documents || []).map((d) => (
            <div key={d.id} className="bg-white rounded shadow p-4">
              <div className="flex items-center gap-3">
                <div className="flex-1">
                  <div className="font-semibold">{d.title}</div>
                  <div className="text-sm text-gray-600">{d.type} • {d.issued_at || '—'} → {d.expires_at || '—'}</div>
                  {expired(d) && (<div className="text-xs text-red-600">Expired</div>)}
                  {d.verified && (<div className="text-xs text-green-700">Verified</div>)}
                </div>
                <div className="flex gap-3 items-center">
                  <a href={`/storage/${d.file}`} target="_blank" rel="noopener" className="text-blue-600 text-sm">View</a>
                  {!d.verified && (
                    <form onSubmit={(e) => { e.preventDefault(); router.post(route('service.drilling.hse.verify', d.id)); }}>
                      <button className="text-green-700 text-sm">Verify</button>
                    </form>
                  )}
                  <form onSubmit={(e) => { e.preventDefault(); router.delete(route('service.drilling.hse.destroy', d.id)); }}>
                    <button className="text-red-600 text-sm">Remove</button>
                  </form>
                </div>
              </div>
            </div>
          ))}
          {(documents || []).length === 0 && (
            <div className="text-sm text-gray-600">No documents yet. Click "Add Document" to upload one.</div>
          )}
        </div>
      </div>
    </div>
  );
}