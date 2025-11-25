import React, { useEffect, useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
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

export default function AuctionsShow({ lot, errors = {}, flash = {} }) {
  const { data, setData, post, processing, reset } = useForm({ amount: '' });
  const preForm = useForm({ amount: '' });
  const inspectionForm = useForm({ type: 'inspection', message: '' });
  const depositForm = useForm({ type: 'deposit', message: '' });

  // Auto-refresh latest bids and current bid every 3 seconds (fallback to polling)
  useEffect(() => {
    if (window?.Echo) return; // Skip polling when Echo is available
    const interval = setInterval(() => {
      router.get(`/auctions/${lot.id}`, {}, { preserveState: true, preserveScroll: true, only: ['lot'] });
    }, 3000);
    return () => clearInterval(interval);
  }, [lot.id]);

  // Add Echo listener for realtime bid updates; fallback to polling when Echo absent
  useEffect(() => {
    if (!lot) return;
  
    // If Echo is available, listen for bid events and refresh the lot
    if (window?.Echo) {
      const channelName = `auction.${lot.id}`;
      const channel = window.Echo.channel(channelName);
      const onBidPlaced = () => {
        router.get(`/auctions/${lot.id}`, {}, { preserveState: true, preserveScroll: true, only: ['lot'] });
      };
      channel.listen('bid.placed', onBidPlaced);
  
      return () => {
        try {
          channel.stopListening('bid.placed', onBidPlaced);
          window.Echo.leave(channelName);
        } catch {}
      };
    }
  
    // Otherwise, keep the 3s polling as a fallback
    const interval = setInterval(() => {
      router.get(`/auctions/${lot.id}`, {}, { preserveState: true, preserveScroll: true, only: ['lot'] });
    }, 3000);
    return () => clearInterval(interval);
  }, [lot?.id]);

  const submitBid = (e) => {
    e.preventDefault();
    post(`/auctions/${lot.id}/bid`, {
      onSuccess: () => reset('amount'),
    });
  };

  const submitPreBid = (e) => {
    e.preventDefault();
    preForm.post(`/auctions/${lot.id}/bid`);
  };

  const buyNow = () => {
    post(`/auctions/${lot.id}/buy-now`);
  };

  const requestInspection = () => {
    inspectionForm.post(`/listings/${lot.id}/request`);
  };

  const requestDeposit = () => {
    depositForm.post(`/listings/${lot.id}/request`);
  };

  const startFinalPayment = () => {
    router.post(`/auctions/${lot.id}/final-payment/checkout`);
  };

  if (!lot) return null;

  const requiresDeposit = (lot.depositPercent || 0) > 0;
  const bidDisabled = processing || (requiresDeposit && !lot.viewerCanBid);
  const isActive = new Date(lot.endAt).getTime() > Date.now();
  const statusLabel = lot.status === 'sold' ? 'Finished' : (isActive ? 'Accepting Pre-bids' : 'Finished');

  const isWinner = !!lot.winnerUserId && lot.viewerUserId && (parseInt(lot.viewerUserId) === parseInt(lot.winnerUserId));

  return (
    <AppLayout>
      <Head title={lot.title} />
      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          <div className="flex justify-between">
            <Link href="/auctions" className="text-sm text-gray-600 hover:text-gray-900">Back to Auctions</Link>
            <Link href="/account" className="text-sm text-indigo-600 hover:text-indigo-800">My Account</Link>
          </div>

          <div className="bg-white shadow sm:rounded-lg p-6">
            <div className="flex items-start justify-between">
              <div>
                <h1 className="text-2xl font-semibold text-gray-900">{lot.title}</h1>
                <p className="mt-1 text-sm text-gray-600">Current Bid: {lot.currency} {lot.currentBid}</p>
                <p className="mt-1 text-xs text-blue-600">{statusLabel}</p>
              </div>
              <Countdown endAt={lot.endAt} />
            </div>

            {flash.success && (
              <div className="mt-3 text-sm text-green-700">{flash.success}</div>
            )}

            {/* Gallery */}
            {Array.isArray(lot.photos) && lot.photos.length > 0 && (
              <div className="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2">
                {lot.photos.map((p, idx) => (
                  <img key={idx} src={p} alt="Photo" className="w-full h-28 object-cover rounded" />
                ))}
              </div>
            )}

            <p className="mt-4 text-gray-700 whitespace-pre-line">{lot.description}</p>

            <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
              <form onSubmit={submitBid} className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">Bid Amount</label>
                <input
                  type="number"
                  value={data.amount}
                  onChange={(e) => setData('amount', e.target.value)}
                  className="w-full border rounded-md px-3 py-2 text-sm"
                  placeholder={`> ${lot.currentBid}`}
                  disabled={requiresDeposit && !lot.viewerCanBid}
                />
                {errors.amount && (
                  <div className="text-sm text-red-600">{errors.amount}</div>
                )}
                <button disabled={bidDisabled} className="px-4 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Make a Bid</button>
                {requiresDeposit && !lot.viewerCanBid && (
                  <p className="text-xs text-gray-500">Bidding locked. Please make a deposit or upgrade.</p>
                )}
                <p className="text-xs text-gray-500">Minimum step: {lot.minStep || 1}</p>
                {lot.timeLimitMinutes ? (
                  <p className="text-xs text-gray-500">Time limit between bids: {lot.timeLimitMinutes} minutes</p>
                ) : null}
              </form>

              <div className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">Pre-Bid Maximum</label>
                <input
                  type="number"
                  value={preForm.data.amount}
                  onChange={(e) => preForm.setData('amount', e.target.value)}
                  className="w-full border rounded-md px-3 py-2 text-sm"
                  placeholder={`Max auto-bid <= ${lot.currency} ${lot.buyNowPrice || ''}`}
                />
                <button onClick={submitPreBid} disabled={preForm.processing} className="w-full px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Set Pre-Bid</button>
                <button onClick={buyNow} className="w-full px-4 py-2 rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">Buy Now</button>
                {(lot.depositPercent || 0) > 0 && (
                  <p className="text-xs text-gray-500">Buy Now will hold a deposit of {(lot.depositPercent * 100).toFixed(0)}% of the buy-now price from your wallet.</p>
                )}
                <Link as="button" method="post" href={`/listings/${lot.id}/favorite`} className="w-full px-4 py-2 rounded-md text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100">Add to Watchlist</Link>
              </div>

              <div className="space-y-2">
                <button onClick={requestInspection} disabled={inspectionForm.processing} className="w-full px-4 py-2 rounded-md text-sm font-medium text-white bg-gray-700 hover:bg-gray-800">Inspection</button>
                <button onClick={requestDeposit} disabled={depositForm.processing} className="w-full px-4 py-2 rounded-md text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700">Deposit</button>
                {lot.inspectionUntil && (
                  <p className="text-xs text-gray-500">Inspection allowed until: {lot.inspectionUntil}</p>
                )}
              </div>
            </div>

            <div className="mt-4 text-sm text-gray-600">
              <p>
                A deposit of {(lot.depositPercent * 100).toFixed(0)}% of your bid amount will be held from your wallet upon placing a bid. If you lose, your deposit is refunded automatically when the auction is finalized.
              </p>
              {!lot.viewerCanBid && (
                <p className="mt-2 text-red-600">You may need to upgrade your plan or top up your wallet to bid.</p>
              )}
            </div>

            {/* Documents */}
            {Array.isArray(lot.documents) && lot.documents.length > 0 && (
              <div className="mt-6">
                <h3 className="text-sm font-medium text-gray-900">Documents</h3>
                <ul className="mt-2 text-sm text-gray-700 space-y-1">
                  {lot.documents.map((d, idx) => (
                    <li key={idx}>
                      <a href={d} target="_blank" rel="noreferrer" className="text-indigo-600 hover:text-indigo-800">Document {idx + 1}</a>
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {/* Bid history */}
            {Array.isArray(lot.bids) && lot.bids.length > 0 && (
              <div className="mt-6">
                <h3 className="text-sm font-medium text-gray-900">Bid history</h3>
                <ul className="mt-2 text-sm text-gray-700 space-y-1">
                  {lot.bids.map((b, idx) => (
                    <li key={idx} className="flex items-center justify-between">
                      <span>{lot.currency} {b.amount}</span>
                      <span className="text-gray-500">{b.created_at}</span>
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {lot.winnerUserId && (
              <p className="mt-3 text-sm text-green-700">Winner determined. Finalized at {lot.finalizedAt || ''}.</p>
            )}

            {isWinner && (
              <div className="mt-4">
                <button onClick={startFinalPayment} className="px-4 py-2 rounded-md text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                  Pay Final Amount
                </button>
                <p className="mt-1 text-xs text-gray-500">Completes payment via Stripe. Your deposit is released to the seller after confirmation.</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}