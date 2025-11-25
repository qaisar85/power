import React from 'react';
import AppLayout from '@/Layouts/AppLayout';

export default function ServiceShow({ service }) {
  return (
    <AppLayout title={service.title}>
      <div className="py-6">
        <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
          <div className="bg-white shadow rounded-lg p-6">
            <h1 className="text-2xl font-bold text-gray-900">{service.title}</h1>
            <div className="mt-2 text-gray-600">Category: {service.category || '—'}</div>
            <div className="mt-2 text-gray-600">Price: {service.price_value} {service.currency} ({service.price_type})</div>
            <div className="mt-4 prose max-w-none">
              <p>{service.description || 'No description provided.'}</p>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}