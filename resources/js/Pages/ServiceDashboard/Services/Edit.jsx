import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ServiceEdit({ company, service }) {
  const { data, setData, put, progress, processing } = useForm({
    title: service.title || '', description: service.description || '', category: service.category || '', subcategory: service.subcategory || '', service_type: service.service_type || '',
    price_type: service.price_type || 'fixed', price_value: service.price_value || '', currency: service.currency || 'USD', price_details: service.price_details || '',
    placement_type: service.placement_type || 'free', visibility: service.visibility || 'pending', geo: service.geo || {}, photos: [], videos: [], pdfs: []
  });

  const submit = (e) => {
    e.preventDefault();
    put(route('service.services.update', service.id));
  };

  return (
    <AppLayout title="Edit Service">
      <Head title="Edit Service" />
      <div className="max-w-4xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Edit Service</h1>
          <Link href={route('service.services.index')} className="text-indigo-600 hover:text-indigo-800">Back to list</Link>
        </div>

        <form onSubmit={submit} encType="multipart/form-data" className="bg-white shadow rounded p-6 space-y-4">
          <div>
            <label className="block text-sm font-medium">Name</label>
            <input className="mt-1 w-full border rounded p-2" value={data.title} onChange={(e)=>setData('title', e.target.value)} required />
          </div>
          <div>
            <label className="block text-sm font-medium">Category</label>
            <input className="mt-1 w-full border rounded p-2" value={data.category} onChange={(e)=>setData('category', e.target.value)} />
          </div>
          <div>
            <label className="block text-sm font-medium">Subcategory</label>
            <input className="mt-1 w-full border rounded p-2" value={data.subcategory} onChange={(e)=>setData('subcategory', e.target.value)} />
          </div>
          <div>
            <label className="block text-sm font-medium">Description</label>
            <textarea className="mt-1 w-full border rounded p-2" rows="5" value={data.description} onChange={(e)=>setData('description', e.target.value)} />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium">Price type</label>
              <select className="mt-1 w-full border rounded p-2" value={data.price_type} onChange={(e)=>setData('price_type', e.target.value)}>
                <option>fixed</option>
                <option>range</option>
                <option>hourly</option>
                <option>formula</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium">Price value</label>
              <input type="number" className="mt-1 w-full border rounded p-2" value={data.price_value} onChange={(e)=>setData('price_value', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Currency</label>
              <select className="mt-1 w-full border rounded p-2" value={data.currency} onChange={(e)=>setData('currency', e.target.value)}>
                <option>USD</option>
                <option>EUR</option>
                <option>AED</option>
              </select>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium">Price details</label>
            <input className="mt-1 w-full border rounded p-2" value={data.price_details} onChange={(e)=>setData('price_details', e.target.value)} />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium">Placement type</label>
              <select className="mt-1 w-full border rounded p-2" value={data.placement_type} onChange={(e)=>setData('placement_type', e.target.value)}>
                <option>free</option>
                <option>paid</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium">Visibility</label>
              <select className="mt-1 w-full border rounded p-2" value={data.visibility} onChange={(e)=>setData('visibility', e.target.value)}>
                <option>pending</option>
                <option>hidden</option>
                <option>published</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium">Service format</label>
              <select className="mt-1 w-full border rounded p-2" value={data.format} onChange={(e)=>setData('format', e.target.value)}>
                <option>On-site</option>
                <option>Remote</option>
                <option>Workshop</option>
              </select>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium">Add photos</label>
            <input type="file" multiple accept="image/*" onChange={(e)=>setData('photos', e.target.files)} />
          </div>
          <div>
            <label className="block text-sm font-medium">Add videos</label>
            <input type="file" multiple accept="video/*" onChange={(e)=>setData('videos', e.target.files)} />
          </div>
          <div>
            <label className="block text-sm font-medium">Add PDFs</label>
            <input type="file" multiple accept="application/pdf" onChange={(e)=>setData('pdfs', e.target.files)} />
          </div>

          <div className="flex items-center justify-end gap-3">
            <Link href={route('service.services.index')} className="px-3 py-2 rounded bg-gray-100 border">Cancel</Link>
            <button type="submit" disabled={processing} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
              {processing ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}