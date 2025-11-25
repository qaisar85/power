import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ProfileIndex({ company }) {
  const form = useForm({
    name: company?.name || '',
    description: company?.description || '',
    website: company?.website || '',
    email: company?.email || '',
    phone: company?.phone || '',
    address: company?.address || {},
    logo: null,
    banner: null,
    sectors: company?.sectors || []
  });

  const submit = (e) => {
    e.preventDefault();
    form.put(route('service.profile.update'));
  };

  return (
    <AppLayout title="Company Profile">
      <Head title="Company Profile" />
      <div className="max-w-4xl mx-auto py-6 px-4">
        <h1 className="text-2xl font-bold mb-6">Company Profile</h1>
        <form onSubmit={submit} encType="multipart/form-data" className="bg-white shadow rounded p-6 space-y-4">
          <div>
            <label className="block text-sm font-medium">Company name</label>
            <input className="mt-1 w-full border rounded p-2" value={form.data.name} onChange={(e)=>form.setData('name', e.target.value)} required />
          </div>
          <div>
            <label className="block text-sm font-medium">Description</label>
            <textarea className="mt-1 w-full border rounded p-2" rows="5" value={form.data.description} onChange={(e)=>form.setData('description', e.target.value)} />
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Website</label>
              <input className="mt-1 w-full border rounded p-2" value={form.data.website} onChange={(e)=>form.setData('website', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Email</label>
              <input type="email" className="mt-1 w-full border rounded p-2" value={form.data.email} onChange={(e)=>form.setData('email', e.target.value)} />
            </div>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Phone</label>
              <input className="mt-1 w-full border rounded p-2" value={form.data.phone} onChange={(e)=>form.setData('phone', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Address (JSON)</label>
              <input className="mt-1 w-full border rounded p-2" value={JSON.stringify(form.data.address)} onChange={(e)=>{
                try { form.setData('address', JSON.parse(e.target.value || '{}')); } catch {}
              }} />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Logo</label>
              <input type="file" accept="image/*" onChange={(e)=>form.setData('logo', e.target.files[0])} />
            </div>
            <div>
              <label className="block text-sm font-medium">Banner</label>
              <input type="file" accept="image/*" onChange={(e)=>form.setData('banner', e.target.files[0])} />
            </div>
          </div>

          <div className="flex items-center justify-end gap-3">
            <button type="submit" disabled={form.processing} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Save Profile</button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
}