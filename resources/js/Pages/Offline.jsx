import React from 'react';
import { Head, Link } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function Offline() {
  return (
    <GuestLayout title="Offline">
      <Head title="Offline" />
      <div className="text-center">
        <h1 className="text-xl font-semibold mb-2">You are offline</h1>
        <p className="text-sm text-gray-600 mb-4">
          Your device is not connected to the internet. Some features may be unavailable.
        </p>
        <Link href="/" className="text-indigo-600 hover:text-indigo-800">Return to home</Link>
      </div>
    </GuestLayout>
  );
}