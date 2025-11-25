import React from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function StocksMarket({ offers, disclaimer }) {
  const { auth } = usePage().props;
  const form = useForm({ shares: '', price_per_share: '' });
  const submit = (e) => {
    e.preventDefault();
    form.post(route('stocks.market.offers.store'));
  };
  return (
    <AppLayout title="Secondary Market">
      <Head title="Secondary Market" />
      <div className="max-w-4xl mx-auto py-8 px-4">
        <h1 className="text-2xl font-semibold mb-4">Secondary Market (Resale)</h1>
        {auth?.user ? (
          <form onSubmit={submit} className="bg-white rounded shadow p-4 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Sell Shares</label>
              <input type="number" min={1} className="mt-1 w-full border rounded px-3 py-2" value={form.data.shares} onChange={(e) => form.setData('shares', e.target.value)} required />
            </div>
            <div>
              <label className="block text-sm font-medium">Price per Share</label>
              <input type="number" step="0.01" className="mt-1 w-full border rounded px-3 py-2" value={form.data.price_per_share} onChange={(e) => form.setData('price_per_share', e.target.value)} required />
            </div>
            <div className="md:col-span-2 flex items-center justify-end gap-2">
              <button type="submit" className="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700" disabled={form.processing}>Create Sell Offer</button>
            </div>
          </form>
        ) : (
          <div className="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
            <div className="font-semibold text-yellow-800">Please login to create sell offers.</div>
            <div className="text-sm text-yellow-700"><Link className="underline" href={route('login')}>Login</Link> or <Link className="underline" href={route('register')}>Register</Link>.</div>
          </div>
        )}

        <div className="bg-white rounded shadow overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Shares</th>
                <th className="text-left p-3">Price per Share</th>
                <th className="text-left p-3">Status</th>
              </tr>
            </thead>
            <tbody>
              {(offers || []).length ? offers.map(o => (
                <tr key={`offer-${o.id}`} className="border-b">
                  <td className="p-3">{o.shares}</td>
                  <td className="p-3">{o.price_per_share}</td>
                  <td className="p-3">{o.status}</td>
                </tr>
              )) : (
                <tr>
                  <td className="p-3 text-center text-gray-500" colSpan={3}>No offers yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        <div className="mt-10 text-xs text-gray-600 border-t pt-4">{disclaimer}</div>
      </div>
    </AppLayout>
  );
}