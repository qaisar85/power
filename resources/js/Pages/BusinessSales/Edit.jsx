import React, { useEffect } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';

export default function Edit({ listing, subcategories }) {
  const bf = listing?.business_fields || {};
  const { data, setData, put, processing } = useForm({
    title: listing?.title || '',
    subcategory: (listing?.subcategories || [])[0] || '',
    country: bf.country || '',
    region: bf.region || '',
    city: bf.city || '',
    short_description: bf.short_description || '',
    full_description: bf.full_description || '',
    target_price: bf.target_price || listing?.price || '',
    currency: bf.currency || listing?.currency || 'USD',
    videos: bf.videos || [],
    photos: [],
    documents: [],
  });

  const submit = (e) => {
    e.preventDefault();
    const formData = new FormData();
    Object.entries(data).forEach(([k, v]) => {
      if (k === 'photos' || k === 'documents') {
        (v || []).forEach((file, idx) => formData.append(`${k}[${idx}]`, file));
      } else if (Array.isArray(v)) {
        v.forEach((val, idx) => formData.append(`${k}[${idx}]`, val));
      } else {
        formData.append(k, v);
      }
    });
    put(route('business.update', listing.id), { data: formData });
  };

  return (
    <div className="max-w-4xl mx-auto py-6 px-4">
      <Head title={`Edit: ${listing?.title || ''}`} />
      <h1 className="text-2xl font-semibold mb-4">Edit Business</h1>
      <form onSubmit={submit} className="space-y-6">
        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Basic</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            <input className="border p-2 rounded" placeholder="Business name" value={data.title} onChange={e => setData('title', e.target.value)} />
            <select className="border p-2 rounded" value={data.subcategory} onChange={e => setData('subcategory', e.target.value)}>
              <option value="">Select subcategory</option>
              {(subcategories || []).map(s => (<option key={s.slug} value={s.slug}>{s.name}</option>))}
            </select>
            <input className="border p-2 rounded" placeholder="Country" value={data.country} onChange={e => setData('country', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Region" value={data.region} onChange={e => setData('region', e.target.value)} />
            <input className="border p-2 rounded" placeholder="City" value={data.city} onChange={e => setData('city', e.target.value)} />
          </div>
        </div>
        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Details</h3>
          <textarea className="border p-2 rounded w-full" rows={2} placeholder="Short description" value={data.short_description} onChange={e => setData('short_description', e.target.value)} />
          <textarea className="border p-2 rounded w-full mt-2" rows={5} placeholder="Full description" value={data.full_description} onChange={e => setData('full_description', e.target.value)} />
        </div>
        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Price & Media</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input className="border p-2 rounded" placeholder="Target price ($)" type="number" value={data.target_price} onChange={e => setData('target_price', e.target.value)} />
            <select className="border p-2 rounded" value={data.currency} onChange={e => setData('currency', e.target.value)}>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
            </select>
            <input className="border p-2 rounded md:col-span-3" placeholder="Video URL" onChange={e => setData('videos', [e.target.value])} />
            <input type="file" multiple accept="image/*" onChange={e => setData('photos', Array.from(e.target.files))} />
            <input type="file" multiple onChange={e => setData('documents', Array.from(e.target.files))} />
          </div>
        </div>

        <div className="flex justify-between">
          <Link href={route('business.show', listing.id)} className="px-3 py-2 bg-gray-100 rounded">Cancel</Link>
          <button type="submit" disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded">Save Changes</button>
        </div>
      </form>
    </div>
  );
}