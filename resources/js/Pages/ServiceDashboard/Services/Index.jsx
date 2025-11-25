import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ServicesIndex({ company, services }) {
  return (
    <AppLayout title="My Services">
      <Head title="My Services" />
      <div className="max-w-6xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">My Services</h1>
          <Link href={route('service.services.create')} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">+ Add Service</Link>
        </div>

        <div className="bg-white shadow rounded">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Service</th>
                <th className="text-left p-3">Category</th>
                <th className="text-left p-3">Status</th>
                <th className="text-left p-3">Placement</th>
                <th className="text-left p-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              {services.data?.length ? services.data.map((s) => (
                <tr key={`s-${s.id}`} className="border-b">
                  <td className="p-3">{s.title}</td>
                  <td className="p-3">{s.category || '-'} / {s.subcategory || '-'}</td>
                  <td className="p-3">{s.visibility}</td>
                  <td className="p-3">{s.placement_type || 'free'}</td>
                  <td className="p-3">
                    <div className="flex gap-2">
                      <Link href={route('service.services.edit', s.id)} className="px-2 py-1 rounded bg-gray-100 border text-sm">Edit</Link>
                      <Link href={route('service.services.publish', s.id)} method="post" as="button" className="px-2 py-1 rounded bg-green-100 border text-sm">Publish</Link>
                      <Link href={route('service.services.destroy', s.id)} method="delete" as="button" className="px-2 py-1 rounded bg-red-100 border text-sm">Delete</Link>
                    </div>
                  </td>
                </tr>
              )) : (
                <tr>
                  <td colSpan="5" className="p-4 text-center text-gray-600">No services yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}