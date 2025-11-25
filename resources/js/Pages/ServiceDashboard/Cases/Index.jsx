import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function CasesIndex({ company, cases }) {
  const delForm = useForm({});
  const onDelete = (id) => {
    if (confirm('Delete this case? This cannot be undone.')) {
      delForm.delete(route('service.cases.destroy', id));
    }
  };

  return (
    <AppLayout title="Cases & Projects">
      <Head title="Cases & Projects" />
      <div className="max-w-6xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Cases & Projects</h1>
          <Link href={route('service.cases.create')} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">+ Add Case</Link>
        </div>

        <div className="bg-white shadow rounded">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Service</th>
                <th className="text-left p-3">Title</th>
                <th className="text-left p-3">Equipment</th>
                <th className="text-left p-3">Location</th>
                <th className="text-left p-3">Date</th>
                <th className="text-left p-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              {cases.data?.length ? cases.data.map((c) => (
                <tr key={`c-${c.id}`} className="border-b">
                  <td className="p-3">{c.service?.title}</td>
                  <td className="p-3">{c.title}</td>
                  <td className="p-3">{c.equipment_type || '-'}</td>
                  <td className="p-3">{c.location || '-'}</td>
                  <td className="p-3">{new Date(c.created_at).toLocaleDateString()}</td>
                  <td className="p-3">
                    <div className="flex gap-2">
                      <Link href={route('service.cases.edit', c.id)} className="px-2 py-1 rounded bg-gray-100 border text-sm">Edit</Link>
                      <button onClick={() => onDelete(c.id)} className="px-2 py-1 rounded bg-red-100 border text-sm">Delete</button>
                    </div>
                  </td>
                </tr>
              )) : (
                <tr>
                  <td colSpan="6" className="p-4 text-center text-gray-600">No cases yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}