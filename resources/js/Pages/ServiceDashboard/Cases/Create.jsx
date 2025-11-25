import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function CaseCreate({ company, services }) {
  const { data, setData, post, processing } = useForm({
    service_id: services[0]?.id || '',
    title: '',
    description: '',
    equipment_type: '',
    location: '',
    photos: [],
    pdfs: []
  });

  const submit = (e) => {
    e.preventDefault();
    post(route('service.cases.store'));
  };

  return (
    <AppLayout title="Add Case">
      <Head title="Add Case" />
      <div className="max-w-4xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Add Case</h1>
          <Link href={route('service.cases.index')} className="text-indigo-600 hover:text-indigo-800">Back to cases</Link>
        </div>

        <form onSubmit={submit} encType="multipart/form-data" className="bg-white shadow rounded p-6 space-y-4">
          <div>
            <label className="block text-sm font-medium">Service</label>
            <select className="mt-1 w-full border rounded p-2" value={data.service_id} onChange={(e)=>setData('service_id', e.target.value)} required>
              {services.map((s) => (
                <option key={`s-${s.id}`} value={s.id}>{s.title}</option>
              ))}
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium">Title</label>
            <input className="mt-1 w-full border rounded p-2" value={data.title} onChange={(e)=>setData('title', e.target.value)} required />
          </div>

          <div>
            <label className="block text-sm font-medium">Description</label>
            <textarea className="mt-1 w-full border rounded p-2" rows="5" value={data.description} onChange={(e)=>setData('description', e.target.value)} />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Equipment type</label>
              <input className="mt-1 w-full border rounded p-2" value={data.equipment_type} onChange={(e)=>setData('equipment_type', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Location</label>
              <input className="mt-1 w-full border rounded p-2" value={data.location} onChange={(e)=>setData('location', e.target.value)} />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium">Photos</label>
            <input type="file" multiple accept="image/*" onChange={(e)=>setData('photos', e.target.files)} />
          </div>

          <div>
            <label className="block text-sm font-medium">PDF files</label>
            <input type="file" multiple accept="application/pdf" onChange={(e)=>setData('pdfs', e.target.files)} />
          </div>

          <div className="flex items-center justify-end gap-3">
            <Link href={route('service.cases.index')} className="px-3 py-2 rounded bg-gray-100 border">Cancel</Link>
            <button type="submit" disabled={processing} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
              {processing ? 'Saving...' : 'Save Case'}
            </button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}