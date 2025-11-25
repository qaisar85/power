import React, { useEffect, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

function Countdown({ endAt }) {
  const [remaining, setRemaining] = useState('');
  useEffect(() => {
    const end = new Date(endAt).getTime();
    const i = setInterval(() => {
      const now = Date.now();
      const diff = Math.max(0, end - now);
      const d = Math.floor(diff / (1000 * 60 * 60 * 24));
      const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
      const m = Math.floor((diff / (1000 * 60)) % 60);
      const s = Math.floor((diff / 1000) % 60);
      setRemaining(`${d}d ${h}h ${m}m ${s}s`);
    }, 1000);
    return () => clearInterval(i);
  }, [endAt]);
  return <span className="text-sm text-gray-600">Ends in: {remaining}</span>;
}

export default function AuctionsIndex({ lots = [], filters = {} }) {
  return (
    <AppLayout>
      <Head title="Auctions" />
      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          <div className="flex justify-end">
            <Link href="/account" className="text-sm text-indigo-600 hover:text-indigo-800">My Account</Link>
          </div>

          <div className="bg-white shadow sm:rounded-lg p-6">
            <div className="flex items-center justify-between">
              <h1 className="text-2xl font-semibold text-gray-900">Auction Lots</h1>
              <Link href="/auctions/sample" className="text-sm text-indigo-600 hover:text-indigo-800">Load Sample</Link>
            </div>
            <p className="mt-1 text-sm text-gray-600">Browse active lots. Timers update live.</p>

            {/* Top filters */}
            <form method="GET" action="/auctions" className="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3">
              <input name="q" defaultValue={filters.q || ''} className="border rounded-md px-3 py-2 text-sm" placeholder="Search" />
              <input name="location" defaultValue={filters.location || ''} className="border rounded-md px-3 py-2 text-sm" placeholder="Location" />
              <input name="category" defaultValue={filters.category || ''} className="border rounded-md px-3 py-2 text-sm" placeholder="Category" />
              <input name="date" defaultValue={filters.date || ''} type="date" className="border rounded-md px-3 py-2 text-sm" />
              <select name="type" defaultValue={filters.type || ''} className="border rounded-md px-3 py-2 text-sm">
                <option value="">All Types</option>
                <option value="Pre-bid">Pre-bid</option>
                <option value="Buy It Now">Buy It Now</option>
                <option value="Finished">Finished</option>
              </select>
              <div className="md:col-span-5 flex justify-end">
                <button className="px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Apply</button>
              </div>
            </form>

            <div className="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
              {lots.map((lot) => {
                const statusColor = lot.status === 'sold'
                  ? 'text-green-700'
                  : lot.auctionType === 'Pre-bid'
                  ? 'text-blue-600'
                  : lot.auctionType === 'Finished'
                  ? 'text-gray-500'
                  : 'text-gray-700';
                return (
                  <div key={lot.id} className="border rounded-lg p-4">
                    <div className="flex items-start justify-between">
                      <div>
                        <h3 className="text-base font-semibold text-gray-900">{lot.title}</h3>
                        <p className="text-sm text-gray-600">{lot.currency} {lot.currentBid}</p>
                        <p className={`text-xs ${statusColor}`}>{lot.auctionType}</p>
                        {(lot.location || lot.category) && (
                          <p className="text-xs text-gray-500">{lot.location || 'Unknown'} • {lot.category || 'General'}</p>
                        )}
                      </div>
                      <Countdown endAt={lot.endAt} />
                    </div>
                    <p className="mt-2 text-sm text-gray-700">{lot.description}</p>
                    <div className="mt-3 flex items-center justify-between">
                      <Link href={`/auctions/${lot.id}`} className="text-sm text-indigo-600 hover:text-indigo-800">Place Bid</Link>
                      <div className="text-right">
                        {lot.buyNowPrice ? (
                          <div className="flex items-center gap-2">
                            <span className="text-xs text-gray-500">Buy Now: {lot.currency} {lot.buyNowPrice}</span>
                            <Link as="button" method="post" href={`/auctions/${lot.id}/buy-now`} className="px-2 py-1 rounded-md text-xs font-medium text-white bg-green-600 hover:bg-green-700">Buy Now</Link>
                          </div>
                        ) : (
                          <span className="text-xs text-gray-400">No Buy Now</span>
                        )}
                      </div>
                    </div>

                    {/* small icons row */}
                    <div className="mt-2 flex items-center gap-3 text-xs text-gray-500">
                      <span title="Inspection">🔍 Inspection</span>
                      <span title="Logistics">🚚 Logistics</span>
                      <Link as="button" method="post" href={`/listings/${lot.id}/favorite`} className="text-indigo-600 hover:text-indigo-800">+ Watchlist</Link>
                    </div>
                  </div>
                );
              })}
              {lots.length === 0 && (
                <div className="text-sm text-gray-500">No lots found. Click Load Sample to create a demo lot.</div>
              )}
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}