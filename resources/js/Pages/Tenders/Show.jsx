import React, { useMemo } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

export default function TenderShow() {
  const { props } = usePage();
  const tender = props.tender || {};
  const canApply = props.canApply;
  const attachments = props.attachments || [];

  const deadlineCountdown = useMemo(() => {
    if (!tender.deadline_at) return null;
    const end = new Date(tender.deadline_at);
    const diff = end.getTime() - Date.now();
    if (diff <= 0) return 'Closed';
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    return `${days}d ${hours}h remaining`;
  }, [tender.deadline_at]);

  const { data, setData, post, processing, errors, reset } = useForm({
    price: '',
    currency: tender.currency || 'USD',
    deadline_days: '',
    comment: '',
    files: [],
  });

  const submit = (e) => {
    e.preventDefault();
    post(`/tenders/${tender.id}/apply`, {
      forceFormData: true,
      onSuccess: () => reset(),
    });
  };

  return (
    <div className="container mx-auto p-6">
      <Head title={tender.title || 'Tender'} />
      <h1 className="text-2xl font-semibold mb-2">{tender.title}</h1>
      <p className="text-sm text-gray-600 mb-1">{tender.category} • {tender.country || tender.location}</p>
      {deadlineCountdown && (
        <div className="mb-3 p-2 bg-yellow-50 border border-yellow-200 rounded">
          Deadline: {deadlineCountdown}
        </div>
      )}
      <p className="whitespace-pre-line text-gray-800 mb-4">{tender.description}</p>

      {attachments.length > 0 && (
        <div className="mb-6">
          <h3 className="font-semibold mb-2">Attachments</h3>
          <ul className="list-disc ml-5">
            {attachments.map((f, idx) => (
              <li key={idx}>
                <a className="text-blue-600" href={`/storage/${f.path}`} target="_blank" rel="noreferrer">{f.name}</a>
              </li>
            ))}
          </ul>
        </div>
      )}

      <div className="bg-white shadow rounded p-4">
        <h3 className="font-semibold mb-2">Submit Proposal</h3>
        {!canApply && (
          <div className="text-gray-600 mb-2">Please sign in and ensure the tender is open.</div>
        )}
        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-2 gap-3">
          <input className="border rounded px-3 py-2" placeholder="Price" value={data.price} onChange={(e) => setData('price', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Currency" value={data.currency} onChange={(e) => setData('currency', e.target.value)} />
          <input className="border rounded px-3 py-2" placeholder="Deadline days" value={data.deadline_days} onChange={(e) => setData('deadline_days', e.target.value)} />
          <textarea className="border rounded px-3 py-2 md:col-span-2" placeholder="Comment" value={data.comment} onChange={(e) => setData('comment', e.target.value)} />
          <input className="border rounded px-3 py-2 md:col-span-2" type="file" multiple onChange={(e) => setData('files', e.target.files)} />
          <button disabled={!canApply || processing} className="bg-blue-600 text-white rounded px-4 py-2 md:col-span-2">Submit Proposal</button>
          {errors && (
            <div className="text-red-600 md:col-span-2">{Object.values(errors).join(', ')}</div>
          )}
        </form>
      </div>
    </div>
  );
}