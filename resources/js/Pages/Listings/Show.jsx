import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ListingShow({ listing, canViewContacts, isOwner }) {
  const requestForm = useForm({ type: 'contact', message: '' });
  const upgradeForm = useForm({ package: 'professional' });
  const favoriteForm = useForm({ type: 'subscribe', meta: { favorite: true } });
  const [showContacts, setShowContacts] = React.useState(canViewContacts);

  const submitRequest = (e) => {
    e.preventDefault();
    requestForm.post(`/listings/${listing.id}/request`, {
      onSuccess: () => requestForm.reset('message'),
    });
  };

  const submitUpgrade = (e) => {
    e.preventDefault();
    upgradeForm.post(`/listings/${listing.id}/upgrade`);
  };

  const addToFavorites = () => {
    favoriteForm.post(`/listings/${listing.id}/favorite`);
  };

  const setType = (type) => requestForm.setData('type', type);

  const paymentLabel = (key) => {
    switch (key) {
      case 'cash': return 'Cash payment';
      case 'bank_transfer': return 'Bank transfer';
      case 'visa_stripe': return 'Through the site (Visa/Stripe)';
      default: return key;
    }
  };

  const formatCond = (c) => ({ new: 'New', used: 'Used', refurbished: 'Refurbished' }[c] || c || '—');
  const formatAvail = (a) => ({ in_stock: 'In stock', on_rent: 'On rent', sold: 'Sold' }[a] || a || '—');

  return (
    <AppLayout>
      <Head title={listing.title} />

      <div className="py-6">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
          {/* Top nav */}
          <div className="flex justify-end">
            <Link href="/account" className="text-sm text-indigo-600 hover:text-indigo-800">My Account</Link>
            <Link href="/account/favorites" className="ml-4 text-sm text-pink-600 hover:text-pink-700">My Favorites</Link>
          </div>

          {/* Header */}
          <div className="bg-white shadow sm:rounded-lg p-6">
            <div className="flex items-start justify-between">
              <div>
                <h1 className="text-2xl font-semibold text-gray-900">{listing.title}</h1>
                <p className="mt-1 text-sm text-gray-600">
                  {Array.isArray(listing.subcategories) && listing.subcategories.length > 0
                    ? `${listing.category} / ${listing.subcategories.join(', ')}`
                    : listing.category}
                  {' '}· {listing.deal_type} · {listing.location}
                </p>
              </div>
              <div className="text-right">
                <div className="text-2xl font-bold text-gray-900">{listing.currency} {listing.price ?? '—'}</div>
                <p className="text-xs text-gray-500">Status: {listing.status}</p>
              </div>
            </div>
          </div>

          {/* Details */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="lg:col-span-2 bg-white shadow sm:rounded-lg p-6">
              <h2 className="text-lg font-medium text-gray-900">Details</h2>
              <p className="mt-3 text-gray-700 whitespace-pre-line">{listing.description || 'No description provided.'}</p>

              {/* Photos */}
              {Array.isArray(listing.photos) && listing.photos.length > 0 ? (
                <div className="mt-4 grid grid-cols-2 md:grid-cols-3 gap-2">
                  {listing.photos.map((src, idx) => (
                    <img key={idx} src={src} alt="photo" className="rounded object-cover h-32 w-full" />
                  ))}
                </div>
              ) : (
                <p className="mt-4 text-sm text-gray-500">No photos uploaded.</p>
              )}

              {/* Product fields */}
              {listing.product_fields && (
                <div className="mt-6">
                  <h3 className="text-sm font-medium text-gray-900">Product details</h3>
                  <div className="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                    <div><span className="text-gray-500">Condition:</span> <span className="text-gray-900">{formatCond(listing.product_fields.condition)}</span></div>
                    <div><span className="text-gray-500">Manufacturer:</span> <span className="text-gray-900">{listing.product_fields.manufacturer || '—'}</span></div>
                    <div><span className="text-gray-500">Model:</span> <span className="text-gray-900">{listing.product_fields.model || '—'}</span></div>
                    <div><span className="text-gray-500">Year:</span> <span className="text-gray-900">{listing.product_fields.year || '—'}</span></div>
                    <div><span className="text-gray-500">Availability:</span> <span className="text-gray-900">{formatAvail(listing.product_fields.availability_status)}</span></div>
                    <div><span className="text-gray-500">Placement:</span> <span className="text-gray-900">{(listing.product_fields.placement_type === 'paid' ? 'Paid' : 'Free') + (listing.product_fields.placement_term ? ` · ${listing.product_fields.placement_term} days` : '')}</span></div>
                  </div>
                </div>
              )}

              {/* Payment options */}
              {Array.isArray(listing.payment_options) && listing.payment_options.length > 0 && (
                <div className="mt-6">
                  <h3 className="text-sm font-medium text-gray-900">Payment methods</h3>
                  <div className="mt-2 flex flex-wrap gap-2">
                    {listing.payment_options.map((p) => (
                      <span key={p} className="px-2 py-1 text-xs rounded bg-gray-100 border">{paymentLabel(p)}</span>
                    ))}
                  </div>
                </div>
              )}
            </div>

            {/* Contact & Actions */}
            <div className="lg:col-span-1 bg-white shadow sm:rounded-lg p-6">
              <h2 className="text-lg font-medium text-gray-900">Seller</h2>
              <div className="mt-2 space-y-1 text-sm">
                <p className="text-gray-900">{listing.seller.name}</p>
                <p className="text-gray-700">Email: {showContacts && canViewContacts ? listing.seller.email : 'hidden@masked'}</p>
                <p className="text-gray-700">Phone: {showContacts && canViewContacts ? listing.seller.phone : '***-***-****'}</p>
                <div className="mt-3">
                  <button
                    type="button"
                    onClick={() => setShowContacts(!showContacts)}
                    disabled={!canViewContacts}
                    className={`inline-flex items-center mt-1 px-3 py-1.5 border text-sm font-medium rounded-md shadow-sm ${canViewContacts ? 'text-white bg-indigo-600 hover:bg-indigo-700 border-indigo-600' : 'text-gray-400 bg-gray-100 border-gray-200 cursor-not-allowed'}`}
                  >
                    {showContacts ? 'Hide owner contacts' : 'Show owner contacts'}
                  </button>
                  {!canViewContacts && (
                    <p className="text-xs text-gray-500 mt-1">Contacts visible only for paid placements or upgraded accounts.</p>
                  )}
                </div>
                {!canViewContacts && (
                  <div className="mt-2">
                    <p className="text-xs text-gray-500">Contacts are masked for Basic package listings.</p>
                    {!isOwner ? (
                      <Link href="/packages" className="inline-flex items-center mt-2 px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                        Unlock contacts
                      </Link>
                    ) : null}
                  </div>
                )}
              </div>

              {/* Owner upgrade controls */}
              {isOwner && (
                <div className="mt-6">
                  <h3 className="text-sm font-medium text-gray-900">Upgrade Listing Package</h3>
                  <form onSubmit={submitUpgrade} className="mt-3 space-y-3">
                    <select
                      value={upgradeForm.data.package}
                      onChange={(e) => upgradeForm.setData('package', e.target.value)}
                      className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                      <option value="professional">Professional</option>
                      <option value="vip">VIP</option>
                      <option value="regional_manager">Regional Manager</option>
                    </select>
                    <button
                      type="submit"
                      disabled={upgradeForm.processing}
                      className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                      {upgradeForm.processing ? 'Upgrading...' : 'Upgrade Package'}
                    </button>
                  </form>
                </div>
              )}

              {/* Actions */}
              <div className="mt-6">
                <h3 className="text-sm font-medium text-gray-900">Request Actions</h3>
                <div className="mt-3 grid grid-cols-2 gap-2">
                  <button
                    className={`px-3 py-1.5 rounded-md text-sm border ${requestForm.data.type === 'contact' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-900 border-gray-300'}`}
                    onClick={() => setType('contact')}
                  >
                    Send Request
                  </button>
                  <button
                    className={`px-3 py-1.5 rounded-md text-sm border ${requestForm.data.type === 'inspection' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-900 border-gray-300'}`}
                    onClick={() => setType('inspection')}
                  >
                    Order Inspection
                  </button>
                  <button
                    className={`px-3 py-1.5 rounded-md text-sm border ${requestForm.data.type === 'shipping' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-900 border-gray-300'}`}
                    onClick={() => setType('shipping')}
                  >
                    Shipping Cost
                  </button>
                  <button
                    className={`px-3 py-1.5 rounded-md text-sm border ${requestForm.data.type === 'subscribe' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-900 border-gray-300'}`}
                    onClick={() => setType('subscribe')}
                  >
                    Subscribe
                  </button>
                </div>

                <div className="mt-3">
                  <button
                    type="button"
                    onClick={addToFavorites}
                    disabled={favoriteForm.processing}
                    className="inline-flex items-center px-3 py-1.5 border text-sm font-medium rounded-md shadow-sm text-white bg-pink-600 hover:bg-pink-700"
                  >
                    {favoriteForm.processing ? 'Adding...' : 'Add to Favorites'}
                  </button>
                  {favoriteForm.recentlySuccessful && (
                    <p className="text-sm text-green-600 mt-1">Added to favorites.</p>
                  )}
                </div>
                <form onSubmit={submitRequest} className="mt-4 space-y-3">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Message</label>
                    <textarea
                      value={requestForm.data.message}
                      onChange={(e) => requestForm.setData('message', e.target.value)}
                      rows={3}
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Add details or questions"
                    />
                  </div>
                  <button
                    type="submit"
                    disabled={requestForm.processing}
                    className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                  >
                    {requestForm.processing ? 'Submitting...' : 'Submit Request'}
                  </button>
                  {requestForm.recentlySuccessful && (
                    <p className="text-sm text-green-600">Request sent.</p>
                  )}
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}