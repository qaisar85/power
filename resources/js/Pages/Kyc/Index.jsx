import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function KycIndex({ kyc, disclaimer }) {
  const form = useForm({
    full_name: kyc?.full_name || '',
    citizenship: kyc?.citizenship || '',
    passport_number: kyc?.passport_number || '',
    country_of_residence: kyc?.country_of_residence || '',
    passport_photo: null,
    selfie_photo: null,
  });
  const submit = (e) => {
    e.preventDefault();
    form.post(route('kyc.store'), { forceFormData: true });
  };
  return (
    <AppLayout title="KYC Verification">
      <Head title="KYC Verification" />
      <div className="max-w-3xl mx-auto py-8 px-4">
        <h1 className="text-2xl font-semibold mb-4">KYC Procedure</h1>
        <div className="bg-white rounded shadow p-4 space-y-4">
          <form onSubmit={submit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium">Full Name</label>
              <input type="text" className="mt-1 w-full border rounded px-3 py-2" value={form.data.full_name} onChange={(e) => form.setData('full_name', e.target.value)} required />
              {form.errors.full_name && <div className="text-red-600 text-sm">{form.errors.full_name}</div>}
            </div>
            <div>
              <label className="block text-sm font-medium">Citizenship</label>
              <input type="text" className="mt-1 w-full border rounded px-3 py-2" value={form.data.citizenship} onChange={(e) => form.setData('citizenship', e.target.value)} required />
              {form.errors.citizenship && <div className="text-red-600 text-sm">{form.errors.citizenship}</div>}
            </div>
            <div>
              <label className="block text-sm font-medium">Passport Number</label>
              <input type="text" className="mt-1 w-full border rounded px-3 py-2" value={form.data.passport_number} onChange={(e) => form.setData('passport_number', e.target.value)} required />
              {form.errors.passport_number && <div className="text-red-600 text-sm">{form.errors.passport_number}</div>}
            </div>
            <div>
              <label className="block text-sm font-medium">Country of Residence</label>
              <input type="text" className="mt-1 w-full border rounded px-3 py-2" value={form.data.country_of_residence} onChange={(e) => form.setData('country_of_residence', e.target.value)} required />
              {form.errors.country_of_residence && <div className="text-red-600 text-sm">{form.errors.country_of_residence}</div>}
            </div>
            <div>
              <label className="block text-sm font-medium">Passport photo (jpeg/pdf)</label>
              <input type="file" accept="image/jpeg,image/png,application/pdf" className="mt-1 w-full" onChange={(e) => form.setData('passport_photo', e.target.files[0])} />
              {form.errors.passport_photo && <div className="text-red-600 text-sm">{form.errors.passport_photo}</div>}
            </div>
            <div>
              <label className="block text-sm font-medium">Selfie with passport (jpeg/png)</label>
              <input type="file" accept="image/jpeg,image/png" className="mt-1 w-full" onChange={(e) => form.setData('selfie_photo', e.target.files[0])} />
              {form.errors.selfie_photo && <div className="text-red-600 text-sm">{form.errors.selfie_photo}</div>}
            </div>
            <div className="flex items-center justify-end gap-2">
              <button type="submit" className="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700" disabled={form.processing}>Upload Documents</button>
            </div>
          </form>
        </div>
        <div className="mt-10 text-xs text-gray-600 border-t pt-4">{disclaimer}</div>
      </div>
    </AppLayout>
  );
}