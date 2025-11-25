import React from 'react';
import { useForm, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ServiceCreate({ defaults }) {
  const { data, setData, post, processing, errors } = useForm({
    title: '', description: '', category: '', subcategories: [], price_type: defaults?.price_type || 'fixed',
    price_value: '', currency: defaults?.currency || 'USD', delivery_days: '', tags: []
  });

  const submit = (e) => {
    e.preventDefault();
    post(route('freelance.services.store'));
  };

  return (
    <AppLayout title="Offer a Service">
      <div className="py-6">
        <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
          <div className="bg-white shadow rounded-lg p-6">
            <div className="flex items-center justify-between mb-4">
              <h1 className="text-2xl font-bold text-gray-900">Create Service</h1>
              <Link href={route('freelance.services.create')} className="text-sm text-gray-600">Help</Link>
            </div>
            <form onSubmit={submit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">Title</label>
                <input className="mt-1 w-full border rounded px-3 py-2" value={data.title} onChange={(e) => setData('title', e.target.value)} />
                {errors.title && <p className="text-sm text-red-600 mt-1">{errors.title}</p>}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700">Description</label>
                <textarea className="mt-1 w-full border rounded px-3 py-2" rows={5} value={data.description} onChange={(e) => setData('description', e.target.value)} />
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Category</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" value={data.category} onChange={(e) => setData('category', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Price Type</label>
                  <select className="mt-1 w-full border rounded px-3 py-2" value={data.price_type} onChange={(e) => setData('price_type', e.target.value)}>
                    <option value="fixed">Fixed</option>
                    <option value="hourly">Hourly</option>
                    <option value="package">Package</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Price Value</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" type="number" step="0.01" value={data.price_value} onChange={(e) => setData('price_value', e.target.value)} />
                </div>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Currency</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" value={data.currency} onChange={(e) => setData('currency', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Delivery Days</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" type="number" min="1" value={data.delivery_days} onChange={(e) => setData('delivery_days', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Tags</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" placeholder="comma,separated,tags" onChange={(e) => setData('tags', e.target.value.split(',').map(t => t.trim()).filter(Boolean))} />
                </div>
              </div>
              <div className="pt-2">
                <button disabled={processing} className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                  {processing ? 'Submitting…' : 'Submit for Moderation'}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}