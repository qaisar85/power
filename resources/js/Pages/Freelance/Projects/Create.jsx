import React from 'react';
import { useForm, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ProjectCreate({ defaults }) {
  const { data, setData, post, processing, errors } = useForm({
    title: '', description: '', category: '', budget_type: defaults?.budget_type || 'fixed', budget_min: '', budget_max: '',
    currency: defaults?.currency || 'USD', location: '', deadline_at: '', attachments: []
  });

  const submit = (e) => {
    e.preventDefault();
    post(route('freelance.projects.store'));
  };

  return (
    <AppLayout title="Post a Project">
      <div className="py-6">
        <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
          <div className="bg-white shadow rounded-lg p-6">
            <div className="flex items-center justify-between mb-4">
              <h1 className="text-2xl font-bold text-gray-900">Create Project</h1>
              <Link href={route('freelance.projects.create')} className="text-sm text-gray-600">Help</Link>
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
                  <label className="block text-sm font-medium text-gray-700">Budget Type</label>
                  <select className="mt-1 w-full border rounded px-3 py-2" value={data.budget_type} onChange={(e) => setData('budget_type', e.target.value)}>
                    <option value="fixed">Fixed</option>
                    <option value="hourly">Hourly</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Currency</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" value={data.currency} onChange={(e) => setData('currency', e.target.value)} />
                </div>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Budget Min</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" type="number" step="0.01" value={data.budget_min} onChange={(e) => setData('budget_min', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Budget Max</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" type="number" step="0.01" value={data.budget_max} onChange={(e) => setData('budget_max', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Deadline</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" type="date" value={data.deadline_at} onChange={(e) => setData('deadline_at', e.target.value)} />
                </div>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Location</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" value={data.location} onChange={(e) => setData('location', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Attachments</label>
                  <input className="mt-1 w-full border rounded px-3 py-2" placeholder="optional" disabled />
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