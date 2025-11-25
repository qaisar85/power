import React, { useState } from 'react';
import { Head, usePage, useForm, router } from '@inertiajs/react';

export default function RigsIndex() {
  const { company, rigs } = usePage().props;
  const [adding, setAdding] = useState(false);

  const form = useForm({
    name: '',
    type: '',
    capacity: '',
    year: '',
    region: '',
    description: '',
    photos: [],
    passports: [],
  });

  const submit = (e) => {
    e.preventDefault();
    form.post(route('service.drilling.rigs.store'), {
      forceFormData: true,
      onSuccess: () => {
        setAdding(false);
        form.reset();
      },
    });
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title={`Fleet — ${company?.name || 'Drilling'}`} />
      <div className="max-w-5xl mx-auto py-8 px-4">
        <div className="flex items-center justify-between mb-4">
          <h1 className="text-xl font-semibold">Fleet (Rigs)</h1>
          <button onClick={() => setAdding((v) => !v)} className="bg-blue-600 text-white px-3 py-2 rounded text-sm">
            {adding ? 'Close' : 'Add Rig'}
          </button>
        </div>

        {adding && (
          <form onSubmit={submit} className="bg-white rounded shadow p-4 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Name</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
            </div>
            <div>
              <label className="block text-sm font-medium">Type</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.type} onChange={(e) => form.setData('type', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Capacity</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.capacity} onChange={(e) => form.setData('capacity', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Year</label>
              <input type="number" min="1900" max="2100" className="mt-1 w-full border rounded px-3 py-2" value={form.data.year} onChange={(e) => form.setData('year', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Region</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.region} onChange={(e) => form.setData('region', e.target.value)} />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium">Description</label>
              <textarea className="mt-1 w-full border rounded px-3 py-2" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Photos</label>
              <input type="file" multiple accept="image/*" onChange={(e) => form.setData('photos', Array.from(e.target.files))} />
            </div>
            <div>
              <label className="block text-sm font-medium">Passports (PDF)</label>
              <input type="file" multiple accept="application/pdf" onChange={(e) => form.setData('passports', Array.from(e.target.files))} />
            </div>
            <div className="md:col-span-2">
              <button type="submit" className="bg-green-600 text-white px-4 py-2 rounded">Save Rig</button>
            </div>
          </form>
        )}

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {(rigs || []).map((r) => (
            <div key={r.id} className="bg-white rounded shadow p-4">
              <div className="flex items-center gap-3">
                {Array.isArray(r.photos) && r.photos[0] ? (
                  <img src={`/storage/${r.photos[0]}`} alt={r.name} className="w-14 h-14 rounded object-cover" />
                ) : (
                  <div className="w-14 h-14 rounded bg-gray-200 flex items-center justify-center text-gray-500">{r.name?.[0] ?? '?'}</div>
                )}
                <div>
                  <div className="font-semibold">{r.name}</div>
                  <div className="text-sm text-gray-600">{r.type || '—'} {r.capacity ? `• ${r.capacity}` : ''}</div>
                  <div className="text-xs text-gray-500">{r.year || ''} {r.region ? `• ${r.region}` : ''}</div>
                </div>
                <form className="ml-auto" onSubmit={(e) => { e.preventDefault(); router.delete(route('service.drilling.rigs.destroy', r.id)); }}>
                  <button className="text-red-600 text-sm">Remove</button>
                </form>
              </div>
            </div>
          ))}
          {(rigs || []).length === 0 && (
            <div className="text-sm text-gray-600">No rigs yet. Click "Add Rig" to create one.</div>
          )}
        </div>
      </div>
    </div>
  );
}