import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function AdminFinanceIndex({ metrics = {}, packages = [], transactions = [], rates = [], pendingManualPayments = [] }) {
  const createForm = useForm({
    name: '', slug: '', description: '', price: '', currency: 'USD', duration_days: 30, features: '[]', is_active: true,
  });

  const rateForm = useForm({ currency: '', usd_rate: '', source: '' });
  const approveForm = useForm({});
  const rejectForm = useForm({});

  const submitCreate = (e) => {
    e.preventDefault();
    createForm.post(route('admin.finance.packages.store'));
  };

  const submitRate = (e) => {
    e.preventDefault();
    rateForm.post(route('admin.finance.rates.store'));
  };

  const { totalRevenue = 0, totalDebits = 0, totalCredits = 0, totalBalances = 0, activePackagesCount = 0 } = metrics;

  return (
    <AppLayout>
      <Head title="Admin — Finance" />
      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          {/* Summary */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <SummaryCard title="Total revenue" value={`$${Number(totalRevenue).toFixed(2)}`} />
            <SummaryCard title="Total credits" value={`$${Number(totalCredits).toFixed(2)}`} />
            <SummaryCard title="Total debits" value={`$${Number(totalDebits).toFixed(2)}`} />
            <SummaryCard title="User balances" value={`$${Number(totalBalances).toFixed(2)}`} />
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <SummaryCard title="Active packages" value={String(activePackagesCount)} />
          </div>

          {/* Prices & tariffs */}
          <section className="bg-white shadow sm:rounded-lg p-6">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-900">Prices & Tariffs</h2>
              <span className="text-sm text-gray-500">Manage paid packages and limits</span>
            </div>
            <div className="mt-4 overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration (days)</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {packages.map(p => (
                    <tr key={p.id}>
                      <td className="px-4 py-2 text-sm text-gray-900">{p.name}</td>
                      <td className="px-4 py-2 text-sm text-gray-900">{p.price}</td>
                      <td className="px-4 py-2 text-sm text-gray-900">{p.currency}</td>
                      <td className="px-4 py-2 text-sm text-gray-900">{p.duration_days}</td>
                      <td className="px-4 py-2 text-sm text-gray-900">{p.is_active ? 'Yes' : 'No'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Create new paid package */}
            <div className="mt-6 border-t pt-6">
              <h3 className="text-base font-semibold text-gray-900">Create new paid package</h3>
              <form onSubmit={submitCreate} className="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Name</label>
                  <input type="text" className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value={createForm.data.name} onChange={e => createForm.setData('name', e.target.value)} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Slug</label>
                  <input type="text" className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value={createForm.data.slug} onChange={e => createForm.setData('slug', e.target.value)} placeholder="auto" />

                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Price</label>
                  <input type="number" step="0.01" className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value={createForm.data.price} onChange={e => createForm.setData('price', e.target.value)} />

                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Currency</label>
                  <input type="text" className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value={createForm.data.currency} onChange={e => createForm.setData('currency', e.target.value)} />

                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Duration (days)</label>
                  <input type="number" className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value={createForm.data.duration_days} onChange={e => createForm.setData('duration_days', e.target.value)} />

                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Features (JSON)</label>
                  <textarea className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" rows={3} value={createForm.data.features} onChange={e => createForm.setData('features', e.target.value)} />

                </div>
                <div className="flex items-center gap-2">
                  <input id="active" type="checkbox" checked={!!createForm.data.is_active} onChange={e => createForm.setData('is_active', e.target.checked)} />
                  <label htmlFor="active" className="text-sm text-gray-700">Active</label>
                </div>
                <div className="md:col-span-2">
                  <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" disabled={createForm.processing}>Create package</button>
                </div>
              </form>
            </div>
          </section>

          {/* Currency Rates */}
          <section className="bg-white shadow sm:rounded-lg p-6">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-900">Currency Rates</h2>
              <span className="text-sm text-gray-500">Store rates to USD</span>
            </div>
            <div className="mt-4 overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">USD Rate</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fetched</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {rates.map(r => (
                    <tr key={r.id} className="odd:bg-gray-50">
                      <td className="px-4 py-2 text-sm text-gray-900">{r.currency}</td>
                      <td className="px-4 py-2 text-sm text-gray-900">{r.usd_rate != null ? Number(r.usd_rate).toFixed(6) : '-'}</td>
                      <td className="px-4 py-2 text-sm text-gray-900">{r.source ?? '-'}</td>
                      <td className="px-4 py-2 text-sm text-gray-900">{r.fetched_at ? new Date(r.fetched_at).toLocaleString() : '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <div className="mt-6 border-t pt-6">
              <h3 className="text-base font-semibold text-gray-900">Add or Update a Rate</h3>
              <form onSubmit={submitRate} className="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Currency</label>
                  <input type="text" className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value={rateForm.data.currency} onChange={e => rateForm.setData('currency', e.target.value.toUpperCase())} placeholder="e.g., EUR" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">USD Rate</label>
                  <input type="number" step="0.00000001" className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value={rateForm.data.usd_rate} onChange={e => rateForm.setData('usd_rate', e.target.value)} placeholder="e.g., 1.0734" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700">Source</label>
                  <input type="text" className="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 md:text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500" value={rateForm.data.source} onChange={e => rateForm.setData('source', e.target.value)} placeholder="e.g., ECB" />
                </div>
                <div className="md:col-span-3">
                  <button type="submit" className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" disabled={rateForm.processing}>Save rate</button>
                </div>
              </form>
            </div>
          </section>

          {/* All transactions */}
          <section className="bg-white shadow sm:rounded-lg p-6">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-900">All Transactions</h2>
              <span className="text-sm text-gray-500">Latest 50 records</span>
            </div>
            <div className="mt-4 overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200 text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance After</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {transactions.map(t => (
                    <tr key={t.id} className="odd:bg-gray-50">
                      <td className="px-4 py-2 text-sm text-gray-900">{t.user?.name} <span className="text-gray-500">({t.user?.email})</span></td>
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
          </section>

          {/* Verification & Notifications */}
          <section className="bg-white shadow sm:rounded-lg p-6">
            <h2 className="text-lg font-semibold text-gray-900">Payment Verification & Notifications</h2>
            <p className="mt-2 text-sm text-gray-600">Bank transfer verification and overdue notifications can be wired by marking incoming transfers as pending and requiring admin approval. Hooks will be added to packages and paid-actions to block usage until payment is confirmed.</p>
          </section>

          {/* Pending Manual Payments */}
          <section className="bg-white shadow sm:rounded-lg p-6">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-semibold text-gray-900">Pending Manual Payments</h2>
              <span className="text-sm text-gray-500">Approve or reject bank/crypto top-ups</span>
            </div>
            {pendingManualPayments.length === 0 ? (
              <p className="mt-2 text-sm text-gray-600">No pending manual payments.</p>
            ) : (
              <div className="mt-4 overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                      <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {pendingManualPayments.map(pr => (
                      <tr key={pr.id} className="odd:bg-gray-50">
                        <td className="px-4 py-2 text-sm text-gray-900">{pr.user?.name} <span className="text-gray-500">({pr.user?.email})</span></td>
                        <td className="px-4 py-2 text-sm text-gray-900">{pr.amount}</td>
                        <td className="px-4 py-2 text-sm text-gray-900">{pr.currency}</td>
                        <td className="px-4 py-2 text-sm text-gray-900">{pr.method}</td>
                        <td className="px-4 py-2 text-sm text-gray-900">{pr.reference ?? '-'}</td>
                        <td className="px-4 py-2 text-sm text-gray-500">{pr.created_at}</td>
                        <td className="px-4 py-2 text-sm">
                          <div className="flex items-center gap-2">
                            <button
                              onClick={() => approveForm.post(route('admin.finance.payments.approve', pr.id))}
                              disabled={approveForm.processing}
                              className="px-3 py-1.5 rounded-md bg-green-600 text-white hover:bg-green-700"
                            >
                              Approve
                            </button>
                            <button
                              onClick={() => rejectForm.post(route('admin.finance.payments.reject', pr.id))}
                              disabled={rejectForm.processing}
                              className="px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700"
                            >
                              Reject
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </section>
        </div>
      </div>
    </AppLayout>
  );
}

function SummaryCard({ title, value }) {
  return (
    <div className="bg-white shadow sm:rounded-lg p-4">
      <div className="text-sm text-gray-500">{title}</div>
      <div className="mt-1 text-xl font-semibold text-gray-900">{value}</div>
    </div>
  );
}