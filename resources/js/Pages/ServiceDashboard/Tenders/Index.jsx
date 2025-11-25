import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

export default function ServiceTendersIndex() {
  const { props } = usePage();
  const needsCompany = props.needs_company;
  const tenders = props.tenders?.data || [];

  const { data, setData, post, processing, reset } = useForm({
    title: '',
    category: '',
    subcategory: '',
    description: '',
    country: '',
    region: '',
    city: '',
    budget_min: '',
    budget_max: '',
    currency: 'USD',
    deadline_at: '',
    visibility: 'public',
    attachments: [],
  });

  const submit = (e) => {
    e.preventDefault();
    post('/service-dashboard/tenders', {
      forceFormData: true,
      onSuccess: () => reset(),
    });
  };

  return (
    <div className="container mx-auto p-6">
      <Head title="My Tenders" />
      <h1 className="text-2xl font-semibold mb-4">My Tenders</h1>

      {needsCompany && (
        <div className="bg-yellow-50 border border-yellow-200 p-3 rounded mb-4">
          Connect a company profile to publish tenders.
        </div>
      )}

      <div className={`bg-white shadow rounded p-4 mb-6 ${needsCompany ? 'opacity-50 pointer-events-none' : ''}`}>
        <h3 className="font-semibold mb-3">Create Tender</h3>
        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-3 gap-3">
          <input className="border rounded px-3 py-2" placeholder="Title" value={data.title} onChange={(e) => setData('title', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Category" value={data.category} onChange={(e) => setData('category', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Subcategory" value={data.subcategory} onChange={(e) => setData('subcategory', e.target.value)} />
          <textarea className="border rounded px-3 py-2 md:col-span-3" placeholder="Description" value={data.description} onChange={(e) => setData('description', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Country" value={data.country} onChange={(e) => setData('country', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Region" value={data.region} onChange={(e) => setData('region', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="City" value={data.city} onChange={(e) => setData('city', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Budget min" value={data.budget_min} onChange={(e) => setData('budget_min', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Budget max" value={data.budget_max} onChange={(e) => setData('budget_max', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Currency" value={data.currency} onChange={(e) => setData('currency', e.target.value)} />
          <input className="border rounded px-3 py-2" type="date" placeholder="Deadline" value={data.deadline_at} onChange={(e) => setData('deadline_at', e.target.value)} />
          <select className="border rounded px-3 py-2" value={data.visibility} onChange={(e) => setData('visibility', e.target.value)}>
            <option value="public">Public</option>
            <option value="link">By link</option>
            <option value="private">Private</option>
          </select>
          <input className="border rounded px-3 py-2 md:col-span-3" type="file" multiple onChange={(e) => setData('attachments', e.target.files)} />
          <button disabled={processing} className="bg-blue-600 text-white rounded px-4 py-2 md:col-span-3">Publish</button>
        </form>
      </div>

      <div className="bg-white shadow rounded p-4">
        <h3 className="font-semibold mb-3">Existing Tenders</h3>
        <table className="w-full text-left">
          <thead>
            <tr>
              <th className="p-2">Title</th>
              <th className="p-2">Status</th>
              <th className="p-2">Applications</th>
              <th className="p-2">Deadline</th>
              <th className="p-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            {tenders.map((t) => (
              <tr key={t.id} className="border-t">
                <td className="p-2">{t.title}</td>
                <td className="p-2">{t.status}</td>
                <td className="p-2">{t.applications_count ?? 0}</td>
                <td className="p-2">{t.deadline_at || '—'}</td>
                <td className="p-2">
                  <a className="px-2 py-1 bg-gray-100 rounded mr-2" href={`/tenders/${t.id}`} target="_blank" rel="noreferrer">View</a>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}