import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function Show({ listing, seo }) {
  const l = listing || {};
  const fin = l.financials || {};
  const op = l.operational || {};
  const sale = l.sale || {};
  const inv = l.investment || {};
  const media = l.media || {};
  const contacts = l.contacts || {};

  const ContactBlock = () => (
    <div className="bg-white rounded shadow p-4">
      <h3 className="font-semibold mb-2">Contacts</h3>
      {contacts.public ? (
        <div className="space-y-1 text-gray-700">
          {contacts.name && <div>Name: {contacts.name}</div>}
          {contacts.phone && <div>Phone: {contacts.phone}</div>}
          {contacts.email && <div>Email: {contacts.email}</div>}
        </div>
      ) : (
        <div className="space-y-2">
          <p className="text-gray-600">Direct contacts available with paid placement or upon request.</p>
          <div className="flex gap-2">
            <Link href={route('listings.request', l.id)} as="button" method="post" data={{ type: 'contact' }} className="px-3 py-2 bg-blue-600 text-white rounded">Request contact</Link>
            <Link href={route('packages.index')} className="px-3 py-2 bg-green-600 text-white rounded">Upgrade</Link>
          </div>
        </div>
      )}
    </div>
  );

  return (
    <div className="max-w-6xl mx-auto py-6 px-4">
      <Head title={seo?.title || l.title} />
      <h1 className="text-2xl font-semibold mb-1">{seo?.h1 || l.title}</h1>
      <p className="text-gray-600 mb-4">{l.subcategory} • {l.location}</p>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="md:col-span-2 space-y-4">
          {media.photos?.length > 0 && (
            <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
              {media.photos.map((p, i) => (
                <img key={i} src={p} alt={`Photo ${i+1}`} className="w-full h-40 object-cover rounded" />
              ))}
            </div>
          )}

          <div className="bg-white rounded shadow p-4">
            <h3 className="font-semibold mb-2">Overview</h3>
            {l.shortDescription && <p className="text-gray-700 mb-2">{l.shortDescription}</p>}
            {l.fullDescription && <p className="text-gray-700 whitespace-pre-line">{l.fullDescription}</p>}
            {l.reasonForSale && <p className="mt-2 text-gray-600">Reason for sale: {l.reasonForSale}</p>}
            {l.growthPotential && <p className="text-gray-600">Growth potential: {l.growthPotential}</p>}
          </div>

          <div className="bg-white rounded shadow p-4">
            <h3 className="font-semibold mb-2">Financials</h3>
            <div className="grid grid-cols-2 gap-2 text-gray-700">
              {fin.annualTurnover && <div>Annual turnover: {fin.annualTurnover}</div>}
              {fin.profit && <div>Profit: {fin.profit}</div>}
              <div>Includes real estate: {fin.includesRealEstate ? 'Yes' : 'No'}</div>
              <div>Assets available: {fin.assetsAvailable ? 'Yes' : 'No'}</div>
              {fin.assetsDescription && <div className="md:col-span-2">Assets: {fin.assetsDescription}</div>}
              <div>Inventory: {fin.inventory ? 'Yes' : 'No'}</div>
              <div>Negotiable: {fin.negotiable ? 'Yes' : 'No'}</div>
              {fin.financialRatios && <div className="md:col-span-2">Ratios: {fin.financialRatios}</div>}
            </div>
          </div>

          <div className="bg-white rounded shadow p-4">
            <h3 className="font-semibold mb-2">Operational</h3>
            <div className="grid grid-cols-2 gap-2 text-gray-700">
              {op.employees && <div>Employees: {op.employees}</div>}
              <div>Licenses available: {op.licensesAvailable ? 'Yes' : 'No'}</div>
              {op.licenseType && <div>License type: {op.licenseType}</div>}
              {op.wells && <div>Wells: {op.wells}</div>}
              {op.avgDailyProduction && <div>Avg daily production: {op.avgDailyProduction}</div>}
              {op.geolocation && <div>Geolocation: {op.geolocation}</div>}
              {op.licenseValidity && <div>License validity: {op.licenseValidity}</div>}
              {op.legalForm && <div>Legal form: {op.legalForm}</div>}
              {op.registrationNumber && <div>Reg No: {op.registrationNumber}</div>}
            </div>
          </div>

          <div className="bg-white rounded shadow p-4">
            <h3 className="font-semibold mb-2">Sale & Investment</h3>
            <div className="grid grid-cols-2 gap-2 text-gray-700">
              {sale.type && <div>Sale type: {sale.type}</div>}
              {sale.buyerType && <div>Buyer type: {sale.buyerType}</div>}
              {sale.debtsCases && <div>Debts / cases: {sale.debtsCases}</div>}
              {inv.roiPotential && <div>ROI potential: {inv.roiPotential}%</div>}
              {inv.participationTerms && <div>Participation: {inv.participationTerms}</div>}
              {inv.investmentTimeline && <div>Timeline: {inv.investmentTimeline}</div>}
              <div>Expansion potential: {inv.expansionPotential ? 'Yes' : 'No'}</div>
              {inv.expansionDescription && <div className="md:col-span-2">Expansion: {inv.expansionDescription}</div>}
            </div>
          </div>

          {media.documents?.length > 0 && (
            <div className="bg-white rounded shadow p-4">
              <h3 className="font-semibold mb-2">Documents</h3>
              <ul className="list-disc pl-5 text-blue-700">
                {media.documents.map((d, i) => (
                  <li key={i}><a href={d} target="_blank" rel="noreferrer">Document {i+1}</a></li>
                ))}
              </ul>
            </div>
          )}
        </div>

        <div className="space-y-4">
          <div className="bg-white rounded shadow p-4">
            <div className="text-gray-600">Status: {l.status}</div>
            <div className="mt-2 text-xl font-bold">{l.price ? `${l.price} ${l.currency || 'USD'}` : 'Price on request'}</div>
            <div className="mt-3 flex gap-2">
              <Link href={route('listings.request', l.id)} as="button" method="post" data={{ type: 'contact' }} className="px-3 py-2 bg-blue-600 text-white rounded">Send request</Link>
              <Link href={route('business.edit', l.id)} className="px-3 py-2 bg-gray-100 rounded">Edit</Link>
              <Link href={route('packages.index')} className="px-3 py-2 bg-green-600 text-white rounded">Upgrade</Link>
            </div>
          </div>

          <ContactBlock />

          {media.videos?.length > 0 && (
            <div className="bg-white rounded shadow p-4">
              <h3 className="font-semibold mb-2">Videos</h3>
              <ul className="list-disc pl-5 text-blue-700">
                {media.videos.map((v, i) => (
                  <li key={i}><a href={v} target="_blank" rel="noreferrer">Video {i+1}</a></li>
                ))}
              </ul>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}