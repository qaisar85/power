import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function RequestsIndex({ company, requests }) {
  const [replyFor, setReplyFor] = useState(null);
  const replyForm = useForm({ message: '', price: '', complete_by: '', attachments: [] });

  const submitReply = (e) => {
    e.preventDefault();
    if (!replyFor) return;
    replyForm.post(route('service.requests.reply', replyFor.id), {
      onSuccess: () => {
        setReplyFor(null);
        replyForm.reset();
      }
    });
  };

  return (
    <AppLayout title="Incoming Requests">
      <Head title="Incoming Requests" />
      <div className="max-w-6xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-bold">Incoming Requests</h1>
          <div className="text-sm text-gray-600">Filter: new / in progress / completed (coming soon)</div>
        </div>

        <div className="bg-white shadow rounded">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Service</th>
                <th className="text-left p-3">Client</th>
                <th className="text-left p-3">Message</th>
                <th className="text-left p-3">Date</th>
                <th className="text-left p-3">Status</th>
                <th className="text-left p-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              {requests.data?.length ? requests.data.map((r) => (
                <tr key={`r-${r.id}`} className="border-b">
                  <td className="p-3">{r.service?.title}</td>
                  <td className="p-3">{r.user?.name || 'Client'}</td>
                  <td className="p-3">{r.message}</td>
                  <td className="p-3">{new Date(r.created_at).toLocaleString()}</td>
                  <td className="p-3">{r.status}</td>
                  <td className="p-3">
                    <div className="flex gap-2">
                      <Link href={route('service.requests.accept', r.id)} method="post" as="button" className="px-2 py-1 rounded bg-green-100 border text-sm">Accept</Link>
                      <Link href={route('service.requests.reject', r.id)} method="post" as="button" className="px-2 py-1 rounded bg-red-100 border text-sm">Reject</Link>
                      <button onClick={() => setReplyFor(r)} className="px-2 py-1 rounded bg-gray-100 border text-sm">Reply</button>
                    </div>
                  </td>
                </tr>
              )) : (
                <tr>
                  <td colSpan="6" className="p-4 text-center text-gray-600">No requests.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {replyFor && (
          <div className="bg-white shadow rounded p-6 mt-6">
            <h2 className="text-lg font-medium mb-2">Reply to request for: {replyFor.service?.title}</h2>
            <form onSubmit={submitReply} encType="multipart/form-data" className="space-y-4">
              <div>
                <label className="block text-sm font-medium">Message</label>
                <textarea className="mt-1 w-full border rounded p-2" rows="4" value={replyForm.data.message} onChange={(e)=>replyForm.setData('message', e.target.value)} required />
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium">Price</label>
                  <input type="number" className="mt-1 w-full border rounded p-2" value={replyForm.data.price} onChange={(e)=>replyForm.setData('price', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium">Completion date</label>
                  <input type="date" className="mt-1 w-full border rounded p-2" value={replyForm.data.complete_by} onChange={(e)=>replyForm.setData('complete_by', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium">Attachments</label>
                  <input type="file" multiple onChange={(e)=>replyForm.setData('attachments', e.target.files)} />
                </div>
              </div>
              <div className="flex gap-3">
                <button type="submit" disabled={replyForm.processing} className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Send Reply</button>
                <button type="button" onClick={() => setReplyFor(null)} className="px-4 py-2 rounded bg-gray-100 border">Cancel</button>
              </div>
            </form>
          </div>
        )}
      </div>
    </AppLayout>
  );
}