import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ listings, filters, subcategories, seo }) {
  const [form, setForm] = useState({
    country: filters.country || '',
    city: filters.city || '',
    subcategory: filters.subcategory || '',
    price_min: filters.price_min ?? '',
    price_max: filters.price_max ?? '',
    turnover_min: filters.turnover_min ?? '',
    turnover_max: filters.turnover_max ?? '',
    status: filters.status || '',
    assets: !!filters.assets,
    real_estate: !!filters.real_estate,
    licenses: !!filters.licenses,
    expansion: !!filters.expansion,
    search: filters.search || '',
  });

  const submitFilters = (e) => {
    e.preventDefault();
    const params = new URLSearchParams();
    Object.entries(form).forEach(([k, v]) => {
      if (v !== '' && v !== null) {
        params.set(k, v);
      }
    });
    router.get(route('business.index') + (params.toString() ? `?${params.toString()}` : ''));
  };

  const Badge = ({ label, active }) => (
    <span className={`px-2 py-1 text-xs rounded ${active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}`}>{label}</span>
  );

  return (
    <div className="max-w-7xl mx-auto py-6 px-4">
      <Head title={seo?.title || 'Business for Sale'} />
      <h1 className="text-2xl font-semibold mb-4">{seo?.h1 || 'Business for Sale'}</h1>
      <p className="text-gray-600 mb-6">{seo?.description}</p>

      <form onSubmit={submitFilters} className="grid grid-cols-1 md:grid-cols-6 gap-3 bg-white p-4 rounded shadow">
        <input className="border p-2 rounded" placeholder="Country" value={form.country} onChange={e => setForm({ ...form, country: e.target.value })} />
        <input className="border p-2 rounded" placeholder="City" value={form.city} onChange={e => setForm({ ...form, city: e.target.value })} />
        <select className="border p-2 rounded" value={form.subcategory} onChange={e => setForm({ ...form, subcategory: e.target.value })}>
          <option value="">Type of business</option>
          {(subcategories || []).map(s => (
            <option key={s.slug} value={s.slug}>{s.name}</option>
          ))}
        </select>
        <input className="border p-2 rounded" placeholder="Price min" type="number" value={form.price_min} onChange={e => setForm({ ...form, price_min: e.target.value })} />
        <input className="border p-2 rounded" placeholder="Price max" type="number" value={form.price_max} onChange={e => setForm({ ...form, price_max: e.target.value })} />
        <input className="border p-2 rounded" placeholder="Search" value={form.search} onChange={e => setForm({ ...form, search: e.target.value })} />
        <input className="border p-2 rounded" placeholder="Turnover min" type="number" value={form.turnover_min} onChange={e => setForm({ ...form, turnover_min: e.target.value })} />
        <input className="border p-2 rounded" placeholder="Turnover max" type="number" value={form.turnover_max} onChange={e => setForm({ ...form, turnover_max: e.target.value })} />
        <select className="border p-2 rounded" value={form.status} onChange={e => setForm({ ...form, status: e.target.value })}>
          <option value="">Any status</option>
          <option value="published">Published</option>
          <option value="under_review">Pending review</option>
        </select>
        <label className="flex items-center space-x-2"><input type="checkbox" checked={form.assets} onChange={e => setForm({ ...form, assets: e.target.checked })} /><span>Assets</span></label>
        <label className="flex items-center space-x-2"><input type="checkbox" checked={form.real_estate} onChange={e => setForm({ ...form, real_estate: e.target.checked })} /><span>Real estate</span></label>
        <label className="flex items-center space-x-2"><input type="checkbox" checked={form.licenses} onChange={e => setForm({ ...form, licenses: e.target.checked })} /><span>Licenses</span></label>
        <label className="flex items-center space-x-2"><input type="checkbox" checked={form.expansion} onChange={e => setForm({ ...form, expansion: e.target.checked })} /><span>Expansion</span></label>
        <button type="submit" className="bg-blue-600 text-white px-4 py-2 rounded">Apply</button>
        <Link href={route('business.create')} className="bg-green-600 text-white px-4 py-2 rounded justify-self-end">Add Business</Link>
      </form>

      <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        {(listings?.data || []).map((b) => (
          <div key={b.id} className="bg-white rounded shadow p-4">
            {b.photo && <img src={b.photo} alt={b.title} className="w-full h-40 object-cover rounded" />}
            <h3 className="mt-2 text-lg font-semibold">{b.title}</h3>
            <p className="text-sm text-gray-600">{b.subcategory} • {b.location}</p>
            {b.shortDescription && <p className="mt-2 text-gray-700">{b.shortDescription}</p>}
            <div className="mt-2 flex gap-2 flex-wrap">
              <Badge label="Includes RE" active={b.badges.includesRealEstate} />
              <Badge label="Licenses" active={b.badges.licenses} />
              <Badge label="Inventory" active={b.badges.inventory} />
              <Badge label="Expansion" active={b.badges.expansion} />
            </div>
            <div className="mt-3 flex items-center justify-between">
              <div className="text-lg font-bold">{b.price ? `${b.price} ${b.currency || 'USD'}` : 'Price on request'}</div>
              <div className="flex gap-2">
                <Link href={route('business.show', b.id)} className="px-3 py-2 bg-gray-100 rounded">More details</Link>
                <Link href={route('listings.request', b.id)} method="post" data={{ type: 'contact' }} as="button" className="px-3 py-2 bg-blue-600 text-white rounded">Send request</Link>
              </div>
            </div>
          </div>
        ))}
      </div>

      {listings?.links && (
        <div className="mt-6 flex gap-2">
          {listings.links.map((l, idx) => (
            <Link key={idx} href={l.url || '#'} className={`px-3 py-1 rounded ${l.active ? 'bg-blue-600 text-white' : 'bg-gray-100'}`} dangerouslySetInnerHTML={{ __html: l.label }} />
          ))}
        </div>
      )}
    </div>
  );
}