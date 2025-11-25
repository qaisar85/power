import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function JobNew() {
  const form = useForm({ title: '', description: '', location: '' });

  const submit = (e) => {
    e.preventDefault();
    form.post('/jobs');
  };

  return (
    <AppLayout>
      <Head title="Post a Job" />
      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          <div className="flex justify-end">
            <Link href="/account" className="text-sm text-indigo-600 hover:text-indigo-800">My Account</Link>
          </div>

          <div className="bg-white shadow sm:rounded-lg p-6">
            <h1 className="text-xl font-semibold text-gray-900">Create Job Posting</h1>
            <p className="mt-1 text-sm text-gray-600">Submit your job for moderation. Status defaults to Under Review.</p>

            <form onSubmit={submit} className="mt-4 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700">Title</label>
                <input
                  type="text"
                  value={form.data.title}
                  onChange={(e) => form.setData('title', e.target.value)}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  value={form.data.description}
                  onChange={(e) => form.setData('description', e.target.value)}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  rows={6}
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Location</label>
                <input
                  type="text"
                  value={form.data.location}
                  onChange={(e) => form.setData('location', e.target.value)}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>

              <div className="flex items-center gap-2">
                <button
                  type="submit"
                  disabled={form.processing}
                  className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                >
                  {form.processing ? 'Submitting...' : 'Submit for Moderation'}
                </button>
                <Link href="/jobs" className="text-sm text-gray-600 hover:text-gray-900">Back to Jobs</Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}