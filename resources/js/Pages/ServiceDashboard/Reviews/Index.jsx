import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ReviewsIndex({ company, reviews, rating }) {
  const [replyingId, setReplyingId] = useState(null);
  const replyForm = useForm({ message: '' });

  const submitReply = (reviewId) => {
    replyForm.post(route('service.reviews.reply', reviewId), {
      onSuccess: () => {
        setReplyingId(null);
        replyForm.reset('message');
      }
    });
  };

  return (
    <AppLayout title="Reviews">
      <Head title="Reviews" />
      <div className="max-w-6xl mx-auto py-6 px-4">
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-bold">Reviews</h1>
            <p className="text-gray-600 text-sm">Average rating: {rating ?? '-'} ★</p>
          </div>
        </div>

        <div className="bg-white shadow rounded">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">User</th>
                <th className="text-left p-3">Stars</th>
                <th className="text-left p-3">Message</th>
                <th className="text-left p-3">Status</th>
                <th className="text-left p-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              {reviews.data?.length ? reviews.data.map((r) => (
                <tr key={`r-${r.id}`} className="border-b">
                  <td className="p-3">{r.user?.name || 'Client'}</td>
                  <td className="p-3">{r.stars}</td>
                  <td className="p-3">
                    <div>{r.message}</div>
                    {r.reply_message ? (
                      <div className="mt-2 p-2 bg-gray-50 border rounded">
                        <div className="text-xs text-gray-500">Reply:</div>
                        <div>{r.reply_message}</div>
                      </div>
                    ) : null}
                  </td>
                  <td className="p-3">{r.status}</td>
                  <td className="p-3 space-x-2">
                    <Link href={route('service.reviews.report', r.id)} method="post" as="button" className="px-2 py-1 rounded bg-red-100 border text-sm">Report</Link>
                    {replyingId === r.id ? (
                      <div className="mt-2">
                        <textarea className="w-full border rounded p-2" rows="2" placeholder="Write a reply" value={replyForm.data.message} onChange={(e)=>replyForm.setData('message', e.target.value)} />
                        <div className="mt-1 flex gap-2">
                          <button type="button" onClick={()=>submitReply(r.id)} disabled={replyForm.processing} className="px-2 py-1 rounded bg-indigo-600 text-white text-sm">Send</button>
                          <button type="button" onClick={()=>{setReplyingId(null); replyForm.reset('message')}} className="px-2 py-1 rounded bg-gray-200 text-sm">Cancel</button>
                        </div>
                      </div>
                    ) : (
                      <button type="button" onClick={()=>{setReplyingId(r.id); replyForm.reset('message')}} className="px-2 py-1 rounded bg-indigo-100 border text-sm">Reply</button>
                    )}
                  </td>
                </tr>
              )) : (
                <tr>
                  <td colSpan="5" className="p-4 text-center text-gray-600">No reviews.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  );
}