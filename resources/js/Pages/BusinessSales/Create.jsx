import React from 'react';
import { Head, useForm, Link } from '@inertiajs/react';

export default function Create({ subcategories }) {
  const { data, setData, post, processing, errors } = useForm({
    title: '',
    subcategory: '',
    country: '',
    region: '',
    city: '',
    year: '',
    ownership_type: '',
    short_description: '',
    full_description: '',
    reason_for_sale: '',
    growth_potential: '',
    annual_turnover: '',
    profit: '',
    assets_available: false,
    assets_description: '',
    inventory: false,
    includes_real_estate: false,
    target_price: '',
    currency: 'USD',
    negotiable: false,
    employees: '',
    licenses_available: false,
    license_type: '',
    number_of_wells: '',
    avg_daily_production: '',
    geolocation: '',
    license_validity_period: '',
    legal_form: '',
    company_registration_number: '',
    sale_type: '',
    buyer_type: '',
    debts_cases: '',
    roi_potential: '',
    participation_terms: '',
    investment_timeline: '',
    expansion_potential: false,
    expansion_description: '',
    videos: [],
    contact_name: '',
    contact_phone: '',
    contact_email: '',
    contact_public: false,
    contact_methods: ['chat'],
    photos: [],
    documents: [],
  });

  const submit = (e) => {
    e.preventDefault();
    const formData = new FormData();
    Object.entries(data).forEach(([k, v]) => {
      if (k === 'photos' || k === 'documents') {
        (v || []).forEach((file, idx) => formData.append(`${k}[${idx}]`, file));
      } else if (Array.isArray(v)) {
        v.forEach((val, idx) => formData.append(`${k}[${idx}]`, val));
      } else {
        formData.append(k, v);
      }
    });
    post(route('business.store'), { data: formData });
  };

  return (
    <div className="max-w-4xl mx-auto py-6 px-4">
      <Head title="Post Business for Sale" />
      <h1 className="text-2xl font-semibold mb-4">Post Business for Sale</h1>
      <form onSubmit={submit} className="space-y-6">
        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Basic Information</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            <input className="border p-2 rounded" placeholder="Business name" value={data.title} onChange={e => setData('title', e.target.value)} />
            <select className="border p-2 rounded" value={data.subcategory} onChange={e => setData('subcategory', e.target.value)}>
              <option value="">Select subcategory</option>
              {(subcategories || []).map(s => (<option key={s.slug} value={s.slug}>{s.name}</option>))}
            </select>
            <input className="border p-2 rounded" placeholder="Country" value={data.country} onChange={e => setData('country', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Region" value={data.region} onChange={e => setData('region', e.target.value)} />
            <input className="border p-2 rounded" placeholder="City" value={data.city} onChange={e => setData('city', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Year of establishment" type="number" value={data.year} onChange={e => setData('year', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Ownership type" value={data.ownership_type} onChange={e => setData('ownership_type', e.target.value)} />
          </div>
        </div>

        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Description</h3>
          <textarea className="border p-2 rounded w-full" rows={2} placeholder="Short description" value={data.short_description} onChange={e => setData('short_description', e.target.value)} />
          <textarea className="border p-2 rounded w-full mt-2" rows={5} placeholder="Full description of activities" value={data.full_description} onChange={e => setData('full_description', e.target.value)} />
          <input className="border p-2 rounded w-full mt-2" placeholder="Reason for sale" value={data.reason_for_sale} onChange={e => setData('reason_for_sale', e.target.value)} />
          <input className="border p-2 rounded w-full mt-2" placeholder="Growth / expansion potential" value={data.growth_potential} onChange={e => setData('growth_potential', e.target.value)} />
        </div>

        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Financial Indicators</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input className="border p-2 rounded" placeholder="Annual turnover ($)" type="number" value={data.annual_turnover} onChange={e => setData('annual_turnover', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Profit (EBITDA / net)" type="number" value={data.profit} onChange={e => setData('profit', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Target price ($)" type="number" value={data.target_price} onChange={e => setData('target_price', e.target.value)} />
            <select className="border p-2 rounded" value={data.currency} onChange={e => setData('currency', e.target.value)}>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
            </select>
            <label className="flex items-center space-x-2"><input type="checkbox" checked={data.negotiable} onChange={e => setData('negotiable', e.target.checked)} /><span>Negotiable</span></label>
            <label className="flex items-center space-x-2"><input type="checkbox" checked={data.includes_real_estate} onChange={e => setData('includes_real_estate', e.target.checked)} /><span>Includes real estate</span></label>
            <label className="flex items-center space-x-2"><input type="checkbox" checked={data.assets_available} onChange={e => setData('assets_available', e.target.checked)} /><span>Assets available</span></label>
            <input className="border p-2 rounded md:col-span-3" placeholder="Assets description" value={data.assets_description} onChange={e => setData('assets_description', e.target.value)} />
            <label className="flex items-center space-x-2"><input type="checkbox" checked={data.inventory} onChange={e => setData('inventory', e.target.checked)} /><span>Inventory</span></label>
          </div>
        </div>

        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Operational Data</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input className="border p-2 rounded" placeholder="Employees" type="number" value={data.employees} onChange={e => setData('employees', e.target.value)} />
            <label className="flex items-center space-x-2"><input type="checkbox" checked={data.licenses_available} onChange={e => setData('licenses_available', e.target.checked)} /><span>Licenses / permits available</span></label>
            <input className="border p-2 rounded" placeholder="License type" value={data.license_type} onChange={e => setData('license_type', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Number of wells" type="number" value={data.number_of_wells} onChange={e => setData('number_of_wells', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Avg daily production" type="number" value={data.avg_daily_production} onChange={e => setData('avg_daily_production', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Geolocation" value={data.geolocation} onChange={e => setData('geolocation', e.target.value)} />
            <input className="border p-2 rounded" placeholder="License validity period" value={data.license_validity_period} onChange={e => setData('license_validity_period', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Legal form" value={data.legal_form} onChange={e => setData('legal_form', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Company Registration Number" value={data.company_registration_number} onChange={e => setData('company_registration_number', e.target.value)} />
          </div>
        </div>

        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Sale & Investment</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input className="border p-2 rounded" placeholder="Type of sale" value={data.sale_type} onChange={e => setData('sale_type', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Type of buyer" value={data.buyer_type} onChange={e => setData('buyer_type', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Debts / encumbrances / legal cases" value={data.debts_cases} onChange={e => setData('debts_cases', e.target.value)} />
            <input className="border p-2 rounded" placeholder="ROI potential (%)" type="number" value={data.roi_potential} onChange={e => setData('roi_potential', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Participation terms" value={data.participation_terms} onChange={e => setData('participation_terms', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Investment timeline" value={data.investment_timeline} onChange={e => setData('investment_timeline', e.target.value)} />
            <label className="flex items-center space-x-2"><input type="checkbox" checked={data.expansion_potential} onChange={e => setData('expansion_potential', e.target.checked)} /><span>Expansion potential</span></label>
            <input className="border p-2 rounded md:col-span-2" placeholder="Expansion description" value={data.expansion_description} onChange={e => setData('expansion_description', e.target.value)} />
          </div>
        </div>

        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Multimedia and Documents</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input className="border p-2 rounded md:col-span-3" placeholder="Video URL" onChange={e => setData('videos', [e.target.value])} />
            <input type="file" multiple accept="image/*" onChange={e => setData('photos', Array.from(e.target.files))} />
            <input type="file" multiple onChange={e => setData('documents', Array.from(e.target.files))} />
          </div>
        </div>

        <div className="bg-white p-4 rounded shadow">
          <h3 className="font-semibold mb-3">Contacts & Consents</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
            <input className="border p-2 rounded" placeholder="Name" value={data.contact_name} onChange={e => setData('contact_name', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Phone" value={data.contact_phone} onChange={e => setData('contact_phone', e.target.value)} />
            <input className="border p-2 rounded" placeholder="Email" value={data.contact_email} onChange={e => setData('contact_email', e.target.value)} />
            <label className="flex items-center space-x-2"><input type="checkbox" checked={data.contact_public} onChange={e => setData('contact_public', e.target.checked)} /><span>Show publicly?</span></label>
          </div>
          <div className="mt-3 text-sm text-gray-600">
            <label className="flex items-center space-x-2"><input type="checkbox" onChange={() => {}} /><span>Owner has the right to post</span></label>
            <label className="flex items-center space-x-2"><input type="checkbox" onChange={() => {}} /><span>Data is accurate</span></label>
            <label className="flex items-center space-x-2"><input type="checkbox" onChange={() => {}} /><span>Consent for moderator verification</span></label>
          </div>
        </div>

        <div className="flex justify-between">
          <Link href={route('business.index')} className="px-3 py-2 bg-gray-100 rounded">Cancel</Link>
          <button type="submit" disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded">Submit for Review</button>
        </div>
      </form>
    </div>
  );
}