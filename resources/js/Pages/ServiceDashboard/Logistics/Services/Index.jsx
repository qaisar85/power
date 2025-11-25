import React, { useState } from 'react';
import { Head, usePage, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function LogisticsServicesIndex() {
  const { company, services, needs_company } = usePage().props;
  const [adding, setAdding] = useState(false);

  const form = useForm({
    title: '',
    description: '',
    subcategory: 'road',
    price_type: 'fixed',
    price_value: '',
    currency: 'USD',
    visibility: 'pending',
    geo: {},
  });

  const submit = (e) => {
    e.preventDefault();
    form.post(route('service.logistics.services.store'), {
      onSuccess: () => {
        setAdding(false);
        form.reset();
      }
    });
  };

  return (
    <AppLayout title="Logistics — Services">
      <Head title={`Logistics Services — ${company?.name || ''}`} />
      <div className="max-w-6xl mx-auto py-8 px-4">
        <div className="flex items-center justify-between mb-4">
          <h1 className="text-xl font-semibold">Logistics Services</h1>
          <button onClick={() => setAdding(v => !v)} className="bg-blue-600 text-white px-3 py-2 rounded text-sm" disabled={needs_company} title={needs_company ? 'Connect a company to add services' : ''}>
            {adding ? 'Close' : 'Add Service'}
          </button>
        </div>

        {needs_company && (
          <div className="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
            <div className="font-semibold text-yellow-800">No company connected</div>
            <div className="text-sm text-yellow-700">Connect a company to manage logistics services.</div>
          </div>
        )}

        {adding && !needs_company && (
          <form onSubmit={submit} className="bg-white rounded shadow p-4 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Title</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} required />
            </div>
            <div>
              <label className="block text-sm font-medium">Subtype</label>
              <select className="mt-1 w-full border rounded px-3 py-2" value={form.data.subcategory} onChange={(e) => form.setData('subcategory', e.target.value)}>
                <option value="road">Road</option>
                <option value="air">Air</option>
                <option value="sea">Sea</option>
                <option value="rail">Rail</option>
                <option value="warehousing">Warehousing</option>
                <option value="customs">Customs</option>
              </select>
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium">Description</label>
              <textarea className="mt-1 w-full border rounded px-3 py-2" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Price Type</label>
              <select className="mt-1 w-full border rounded px-3 py-2" value={form.data.price_type} onChange={(e) => form.setData('price_type', e.target.value)}>
                <option value="fixed">Fixed</option>
                <option value="range">Range</option>
                <option value="hourly">Hourly</option>
                <option value="formula">Formula</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium">Price Value</label>
              <input type="number" step="0.01" className="mt-1 w-full border rounded px-3 py-2" value={form.data.price_value} onChange={(e) => form.setData('price_value', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Currency</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.currency} onChange={(e) => form.setData('currency', e.target.value)} />
            </div>
            <div className="md:col-span-2 flex items-center justify-end gap-2">
              <button type="submit" className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700" disabled={form.processing}>Create</button>
            </div>
          </form>
        )}

        {/* Services table */}
        <div className="bg-white rounded shadow">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Service</th>
                <th className="text-left p-3">Subtype</th>
                <th className="text-left p-3">Price</th>
                <th className="text-left p-3">Visibility</th>
              </tr>
            </thead>
            <tbody>
              {(services || []).length ? services.map((s) => (
                <tr key={`s-${s.id}`} className="border-b">
                  <td className="p-3">{s.title}</td>
                  <td className="p-3">{s.subcategory || '-'}</td>
                  <td className="p-3">{s.price_value ? `${s.price_value} ${s.currency || 'USD'}` : '-'}</td>
                  <td className="p-3">{s.visibility}</td>
                </tr>
              )) : (
                <tr>
                  <td className="p-4 text-center text-gray-500" colSpan={4}>No logistics services yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}