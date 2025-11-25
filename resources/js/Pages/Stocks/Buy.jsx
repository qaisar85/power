import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function StocksBuy({ kyc_status, needs_kyc, disclaimer }) {
  const form = useForm({ shares: '', payment_method: 'stripe', agree_terms: false });
  const submit = (e) => {
    e.preventDefault();
    form.post(route('stocks.buy.store'), { forceFormData: true });
  };
  return (
    <AppLayout title="Buy WOP Shares">
      <Head title="Buy Shares" />
      <div className="max-w-3xl mx-auto py-8 px-4">
        <h1 className="text-2xl font-semibold mb-4">Purchase of shares</h1>
        {needs_kyc && (
          <div className="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
            <div className="font-semibold text-yellow-800">Please complete KYC before purchasing shares.</div>
            <div className="text-sm text-yellow-700">Current status: {kyc_status}. <Link href={route('kyc.index')} className="underline">Open KYC form</Link>.</div>
          </div>
        )}
        {!needs_kyc && (
          <form onSubmit={submit} className="bg-white rounded shadow p-4 space-y-4">
            <div>
              <label className="block text-sm font-medium">Number of Shares</label>
              <input type="number" min={1} className="mt-1 w-full border rounded px-3 py-2" value={form.data.shares} onChange={(e) => form.setData('shares', e.target.value)} required />
            </div>
            <div>
              <label className="block text-sm font-medium">Payment Method</label>
              <select className="mt-1 w-full border rounded px-3 py-2" value={form.data.payment_method} onChange={(e) => form.setData('payment_method', e.target.value)}>
                <option value="stripe">Stripe (VISA/MasterCard)</option>
                <option value="yookassa">YooKassa</option>
                <option value="crypto">Cryptocurrencies (USDT, BTC)</option>
                <option value="bank">Bank Transfer (manual confirmation)</option>
              </select>
            </div>
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <input id="agree" type="checkbox" checked={form.data.agree_terms} onChange={(e) => form.setData('agree_terms', e.target.checked)} />
                <label htmlFor="agree" className="text-sm">I confirm that I have read and accepted the Investment Agreement</label>
              </div>
              <div className="text-xs text-gray-600 flex flex-wrap gap-3">
                <Link href="/docs/share-purchase-agreement.pdf" className="underline" target="_blank" rel="noreferrer">Shareholder Agreement</Link>
                <Link href="/docs/risk-disclosure.pdf" className="underline" target="_blank" rel="noreferrer">Risk Disclosure</Link>
                <Link href="/docs/privacy-policy.pdf" className="underline" target="_blank" rel="noreferrer">Privacy Policy</Link>
              </div>
            </div>
            {form.errors.agree_terms && <div className="text-red-600 text-sm">{form.errors.agree_terms}</div>}
            <div className="flex items-center justify-end gap-2">
              <button type="submit" className="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700" disabled={form.processing}>Confirm Purchase</button>
            </div>
          </form>
        )}
        <div className="mt-10 text-xs text-gray-600 border-t pt-4">{disclaimer}</div>
      </div>
    </AppLayout>
  );
}