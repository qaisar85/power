import React, { useMemo, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function PackagesIndex({ wallet, transactions = [], packages = [], userPackage = 'basic', activeUserPackage = null }) {
  const topupForm = useForm({ amount: '', method: 'bank_transfer', reference: '', currency: (wallet?.currency || 'USD') });
  const subscribeForm = useForm({ package_id: null, promo_code: '' });
  const [cardProvider, setCardProvider] = useState('stripe');

  // Build CSV for download
  const csv = React.useMemo(() => {
    const header = ['Date', 'Action', 'Amount', 'Type', 'Balance After'];
    const rows = (transactions || []).map(t => [
      new Date(t.created_at).toLocaleString(),
      t.description || '-',
      `${t.amount}`,
      t.type,
      t.balance_after ?? ''
    ]);
    return [header, ...rows].map(r => r.join(',')).join('\n');
  }, [transactions]);

  const downloadCsv = () => {
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'transaction_history.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };

  const submitTopup = (e) => {
    e.preventDefault();
    if (topupForm.data.method === 'card') {
      const provider = cardProvider || 'stripe';
      const amount = Number(topupForm.data.amount || 0);
      const currency = (topupForm.data.currency || 'USD');
      const url = provider === 'paypal'
        ? route('wallet.topup.paypal', { amount, currency })
        : route('wallet.topup.card', { amount, currency });
      window.location.href = url;
      return;
    }
    // Manual topup submission
    topupForm.post(route('wallet.topup.manual'));
  };

  const activatePackage = (pkg) => {
    subscribeForm.setData('package_id', pkg.id);
    subscribeForm.post(route('packages.subscribe'));
  };

  return (
    <AppLayout>
      <Head title="Tariffs & Packages" />

      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          {/* Header */}
          <div className="bg-white shadow sm:rounded-lg p-6 flex items-center justify-between">
            <div>
              <h1 className="text-2xl font-semibold text-gray-900">Tariffs & Packages</h1>
              <p className="mt-1 text-sm text-gray-600">Choose your package and manage your wallet balance.</p>
            </div>
            <div className="text-sm text-gray-700">
              <span className="font-medium">Current plan:</span> {activeUserPackage?.package?.name || userPackage}
            </div>
          </div>

          {/* Wallet & Top-up */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-1 bg-white shadow sm:rounded-lg p-6">
              <h2 className="text-lg font-medium text-gray-900">Wallet</h2>
              <p className="mt-2 text-sm text-gray-600">Balance available for purchasing packages.</p>

              <div className="mt-4">
                <div className="flex items-end gap-2">
                  <span className="text-3xl font-bold text-gray-900">{wallet?.balance ?? '0.00'}</span>
                  <span className="text-sm text-gray-500">{wallet?.currency ?? 'USD'}</span>
                </div>
              </div>

              <form onSubmit={submitTopup} className="mt-6 space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Top-up amount</label>
                  <input
                    type="number"
                    step="0.01"
                    min="1"
                    value={topupForm.data.amount}
                    onChange={(e) => topupForm.setData('amount', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Enter amount"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Payment Method</label>
                  <select
                    value={topupForm.data.method}
                    onChange={(e) => topupForm.setData('method', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="crypto">Cryptocurrency</option>
                    <option value="card">Bank Card (Stripe/PayPal)</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Currency</label>
                  <select
                    value={topupForm.data.currency}
                    onChange={(e) => topupForm.setData('currency', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  >
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="AED">AED</option>
                  </select>
                </div>
                {topupForm.data.method === 'card' && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Card provider</label>
                    <select
                      value={cardProvider}
                      onChange={(e) => setCardProvider(e.target.value)}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                      <option value="stripe">Stripe</option>
                      <option value="paypal">PayPal</option>
                    </select>
                  </div>
                )}
                {(topupForm.data.method === 'bank_transfer' || topupForm.data.method === 'crypto') && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Payment Reference (optional)</label>
                    <input
                      type="text"
                      value={topupForm.data.reference}
                      onChange={(e) => topupForm.setData('reference', e.target.value)}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="e.g., transfer number or transaction hash"
                    />
                    <p className="text-xs text-gray-500 mt-1">We will verify your payment and credit your wallet upon approval.</p>
                  </div>
                )}
                <button
                  type="submit"
                  disabled={topupForm.processing}
                  className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
                >
                  {topupForm.processing ? 'Processing...' : 'Top up wallet'}
                </button>
                {topupForm.errors?.amount && (
                  <p className="text-sm text-red-600">{topupForm.errors.amount}</p>
                )}
                {topupForm.recentlySuccessful && (
                  <p className="text-sm text-green-600">Balance updated.</p>
                )}
              </form>
            </div>

            {/* Packages */}
            <div className="lg:col-span-2 bg-white shadow sm:rounded-lg p-6">
              <h2 className="text-lg font-medium text-gray-900">Available Packages</h2>
              <div className="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                {packages.map((pkg) => {
                  const isCurrent = (activeUserPackage?.package?.slug || userPackage) === (pkg.slug || pkg.key);
                  return (
                    <div key={pkg.id || pkg.slug || pkg.key} className={`border rounded-lg p-4 ${isCurrent ? 'border-indigo-300' : ''}`}>
                      <div className="flex items-center justify-between">
                        <div>
                          <h3 className="text-base font-semibold text-gray-900">{pkg.name}</h3>
                          <p className="text-sm text-gray-500">{pkg.currency} {pkg.price}</p>
                        </div>
                        <button
                          className={`px-3 py-1.5 rounded-md text-sm ${isCurrent ? 'bg-gray-200 text-gray-700 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700'}`}
                          onClick={() => !isCurrent && activatePackage(pkg)}
                          disabled={isCurrent || subscribeForm.processing}
                        >
                          {isCurrent ? 'Current Plan' : 'Purchase plan'}
                        </button>
                      </div>
                      {!isCurrent && (
                        <div className="mt-3">
                          <label className="block text-sm font-medium text-gray-700">Promo code (optional)</label>
                          <input
                            type="text"
                            value={subscribeForm.data.promo_code}
                            onChange={(e) => subscribeForm.setData('promo_code', e.target.value)}
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Enter promo code if you have one"
                          />
                          {subscribeForm.errors?.promo_code && (
                            <p className="text-sm text-red-600">{subscribeForm.errors.promo_code}</p>
                          )}
                        </div>
                      )}
                      <ul className="mt-3 list-disc pl-5 text-sm text-gray-700 space-y-1">
                        {(pkg.features || []).map((f, idx) => (
                          <li key={idx}>{typeof f === 'string' ? f : JSON.stringify(f)}</li>
                        ))}
                      </ul>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>

          {/* Transactions */}
          <div className="bg-white shadow sm:rounded-lg p-6">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-medium text-gray-900">Transaction History</h2>
              <div className="flex items-center gap-2">
                <button onClick={downloadCsv} className="px-3 py-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">Download CSV</button>
                <button onClick={() => window.print()} className="px-3 py-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200">Download PDF</button>
              </div>
            </div>
            {transactions.length === 0 ? (
              <p className="mt-2 text-sm text-gray-600">No transactions yet.</p>
            ) : (
              <div className="mt-4 overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance After</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {transactions.map((t) => (
                      <tr key={t.id}>
                        <td className="px-4 py-2 text-sm text-gray-900">{t.type}</td>
                        <td className="px-4 py-2 text-sm text-gray-900">{t.amount}</td>
                        <td className="px-4 py-2 text-sm text-gray-900">{t.balance_after ?? '-'}</td>
                        <td className="px-4 py-2 text-sm text-gray-700">{t.description ?? '-'}</td>
                        <td className="px-4 py-2 text-sm text-gray-500">{t.created_at}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
function PackagesTopUpPreview({ wallet, packages, transactions }) {
  const [amount, setAmount] = useState('');
  const [currency, setCurrency] = useState(wallet?.currency || 'USD');
  const [method, setMethod] = useState('bank_transfer');
  const [reference, setReference] = useState('');
  const [cardProvider, setCardProvider] = useState('stripe');

  const handleTopUp = (e) => {
    e.preventDefault();
    const payload = { amount: parseFloat(amount), currency };
    if (method === 'bank_transfer' || method === 'crypto') {
      payload.method = method;
      if (reference) payload.reference = reference;
      router.post(route('wallet.topup.manual'), payload);
    } else if (method === 'card') {
      const url = (cardProvider === 'paypal')
        ? route('wallet.topup.paypal', { amount: parseFloat(amount), currency })
        : route('wallet.topup.card', { amount: parseFloat(amount), currency });
      window.location.href = url;
    }
  };

  return (
    <div>
      {/* Wallet balance */}
      <div className="mb-6">
        <h2 className="text-xl font-semibold">My Balance</h2>
        <p className="text-gray-700">Balance: {wallet?.balance ?? 0} {wallet?.currency ?? 'USD'}</p>
      </div>

      {/* Top Up form */}
      <form onSubmit={handleTopUp} className="p-4 border rounded mb-8">
        <h3 className="font-medium mb-3">Top Up Account</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label className="block text-sm">Amount</label>
            <input type="number" step="0.01" min="1" value={amount} onChange={(e) => setAmount(e.target.value)} className="input input-bordered w-full" required />
          </div>
          <div>
            <label className="block text-sm">Currency</label>
            <select value={currency} onChange={(e) => setCurrency(e.target.value)} className="select select-bordered w-full">
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
              <option value="AED">AED</option>
            </select>
          </div>
          <div>
            <label className="block text-sm">Payment Method</label>
            <select value={method} onChange={(e) => setMethod(e.target.value)} className="select select-bordered w-full">
              <option value="bank_transfer">Bank Transfer</option>
              <option value="crypto">Cryptocurrency</option>
              <option value="card">Bank Card (Stripe/PayPal)</option>
            </select>
          </div>
        </div>
        {(method === 'bank_transfer' || method === 'crypto') && (
          <div className="mt-3">
            <label className="block text-sm">Payment Reference (optional)</label>
            <input type="text" value={reference} onChange={(e) => setReference(e.target.value)} className="input input-bordered w-full" placeholder="e.g., transfer number or transaction hash" />
            <p className="text-xs text-gray-500 mt-1">We will verify your payment and credit your wallet upon approval.</p>
          </div>
        )}
        <button type="submit" className="btn btn-primary mt-4">Top Up</button>
      </form>

      {/* Packages listing */}
      {/* ... existing packages UI ... */}

      {/* Transactions table */}
      {/* ... existing transactions UI ... */}
    </div>
  );
}