import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function DocumentsIndex({ company, documents }) {
  const form = useForm({ type: '', file: null, expires_at: '' });

  const submit = (e) => {
    e.preventDefault();
    form.post(route('service.documents.store'));
  };

  return (
    <AppLayout title="Certificates & Documents">
      <Head title="Certificates & Documents" />
      <div className="max-w-6xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Certificates & Documents</h1>
        </div>

        <div className="bg-white shadow rounded p-6 mb-6">
          <h2 className="text-lg font-medium mb-2">Upload new document</h2>
          <form onSubmit={submit} encType="multipart/form-data" className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
              <label className="block text-sm font-medium">Type</label>
              <input className="mt-1 w-full border rounded p-2" value={form.data.type} onChange={(e)=>form.setData('type', e.target.value)} placeholder="e.g. ISO 9001" required />
            </div>
            <div>
              <label className="block text-sm font-medium">Expires</label>
              <input type="date" className="mt-1 w-full border rounded p-2" value={form.data.expires_at} onChange={(e)=>form.setData('expires_at', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">File</label>
              <input type="file" className="mt-1 w-full" onChange={(e)=>form.setData('file', e.target.files[0])} required />
            </div>
            <div>
              <button type="submit" disabled={form.processing} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Upload</button>
            </div>
          </form>
        </div>

        <div className="bg-white shadow rounded">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Type</th>
                <th className="text-left p-3">Status</th>
                <th className="text-left p-3">Expires</th>
                <th className="text-left p-3">File</th>
                <th className="text-left p-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              {documents.data?.length ? documents.data.map((d) => (
                <tr key={`d-${d.id}`} className="border-b">
                  <td className="p-3">{d.type}</td>
                  <td className="p-3">
                    {d.status}
                    {d.is_rejected ? (
                      <span className="ml-2 px-2 py-0.5 text-xs rounded bg-red-100 text-red-700 border">Rejected</span>
                    ) : null}
                  </td>
                  <td className="p-3">{d.expires_at ? new Date(d.expires_at).toLocaleDateString() : '-'}</td>
                  <td className="p-3">
                    <a href={d.filename?.startsWith('http') ? d.filename : `/storage/${d.filename}`} target="_blank" rel="noreferrer" className="text-indigo-600">View</a>
                  </td>
                  <td className="p-3 space-x-2">
                    {!d.is_rejected && d.status === 'pending' && (
                      <Link href={route('service.documents.verify', d.id)} method="post" as="button" className="px-2 py-1 rounded bg-green-100 border text-sm">Verify</Link>
                    )}
                    {!d.is_rejected && d.status !== 'verified' && (
                      <Link href={route('service.documents.reject', d.id)} method="post" as="button" className="px-2 py-1 rounded bg-red-100 border text-sm">Reject</Link>
                    )}
                  </td>
                </tr>
              )) : (
                <tr>
                  <td colSpan="5" className="p-4 text-center text-gray-600">No documents uploaded.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}