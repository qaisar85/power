import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function CaseEdit({ company, case: item, services }) {
  const { data, setData, put, processing } = useForm({
    service_id: item?.service_id || services[0]?.id || '',
    title: item?.title || '',
    description: item?.description || '',
    equipment_type: item?.equipment_type || '',
    location: item?.location || '',
    photos: [],
    pdfs: [],
    remove_photos: [],
    remove_pdfs: [],
  });

  const submit = (e) => {
    e.preventDefault();
    put(route('service.cases.update', item.id));
  };

  const existingPhotos = (item?.files?.photos || []);
  const existingPdfs = (item?.files?.pdfs || []);

  const toggleRemovePhoto = (path) => {
    const next = data.remove_photos.includes(path)
      ? data.remove_photos.filter(p => p !== path)
      : [...data.remove_photos, path];
    setData('remove_photos', next);
  };

  const toggleRemovePdf = (path) => {
    const next = data.remove_pdfs.includes(path)
      ? data.remove_pdfs.filter(p => p !== path)
      : [...data.remove_pdfs, path];
    setData('remove_pdfs', next);
  };

  return (
    <AppLayout title="Edit Case">
      <Head title="Edit Case" />
      <div className="max-w-4xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Edit Case</h1>
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
            <label className="block text-sm font-medium">Existing Photos</label>
            <div className="mt-1 flex flex-wrap gap-2">
              {existingPhotos.length ? existingPhotos.map((p, idx) => (
                <div key={`p-${idx}`} className="flex items-center gap-2">
                  <a href={p.startsWith('http') ? p : `/storage/${p}`} target="_blank" rel="noreferrer" className={`text-sm ${data.remove_photos.includes(p) ? 'line-through text-gray-400' : 'text-indigo-600'}`}>Photo #{idx+1}</a>
                  <button type="button" onClick={()=>toggleRemovePhoto(p)} className={`px-2 py-1 rounded text-xs border ${data.remove_photos.includes(p) ? 'bg-red-50 border-red-300 text-red-700' : 'bg-gray-50 border-gray-300 text-gray-700'}`}>
                    {data.remove_photos.includes(p) ? 'Undo Remove' : 'Remove'}
                  </button>
                </div>
              )) : <span className="text-gray-600 text-sm">None</span>}
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium">Add Photos</label>
            <input type="file" multiple accept="image/*" onChange={(e)=>setData('photos', e.target.files)} />
          </div>

          <div>
            <label className="block text-sm font-medium">Existing PDFs</label>
            <div className="mt-1 flex flex-wrap gap-2">
              {existingPdfs.length ? existingPdfs.map((p, idx) => (
                <div key={`d-${idx}`} className="flex items-center gap-2">
                  <a href={p.startsWith('http') ? p : `/storage/${p}`} target="_blank" rel="noreferrer" className={`text-sm ${data.remove_pdfs.includes(p) ? 'line-through text-gray-400' : 'text-indigo-600'}`}>PDF #{idx+1}</a>
                  <button type="button" onClick={()=>toggleRemovePdf(p)} className={`px-2 py-1 rounded text-xs border ${data.remove_pdfs.includes(p) ? 'bg-red-50 border-red-300 text-red-700' : 'bg-gray-50 border-gray-300 text-gray-700'}`}>
                    {data.remove_pdfs.includes(p) ? 'Undo Remove' : 'Remove'}
                  </button>
                </div>
              )) : <span className="text-gray-600 text-sm">None</span>}
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium">Add PDFs</label>
            <input type="file" multiple accept="application/pdf" onChange={(e)=>setData('pdfs', e.target.files)} />
          </div>

          <div className="flex items-center justify-end gap-3">
            <Link href={route('service.cases.index')} className="px-3 py-2 rounded bg-gray-100 border">Cancel</Link>
            <button type="submit" disabled={processing} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
              {processing ? 'Updating...' : 'Update Case'}
            </button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}