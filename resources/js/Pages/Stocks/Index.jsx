import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function StocksIndex({ disclaimer }) {
  return (
    <AppLayout title="STOCKS — World Oil Portal">
      <Head title="STOCKS" />
      <div className="max-w-4xl mx-auto py-8 px-4">
        <h1 className="text-2xl font-semibold mb-4">STOCKS ⭐️ Asset Type — Sale of Shares</h1>
        <p className="text-gray-700 mb-4">Sale of shares / equity / tokenized part of World Oil Portal. Automatic acceptance of investments from individuals through the website. Creation of a personal investor dashboard. Resale of shares to other investors (P2P, secondary market). Automatic maintenance of the investor and transaction registry.</p>
        <div className="space-x-2 mb-6">
          <Link href={route('login')} className="px-4 py-2 rounded bg-slate-100 hover:bg-slate-200">Investor Login / Register</Link>
          <Link href={route('stocks.buy')} className="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Buy ( WOP ) Shares</Link>
          <Link href={route('stocks.market')} className="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700">Secondary Market</Link>
        </div>
        <div className="space-y-2">
          <div className="font-semibold">Examples of Buttons and Messages</div>
          <ul className="list-disc list-inside text-sm text-gray-700">
            <li>Buttons: Investor Login / Register, Buy ( WOP ) Shares, Confirm Purchase, Upload Documents, Create Sell Offer, Sell Shares</li>
            <li>Messages: "Please complete KYC before purchasing shares.", "Payment successful! Certificate will be sent to your email.", "Insufficient shares available."</li>
            <li>Errors: "Invalid passport photo format.", "KYC verification pending."</li>
          </ul>
        </div>
        <div className="mt-10 text-xs text-gray-600 border-t pt-4">{disclaimer}</div>
      </div>
    </AppLayout>
  );
}