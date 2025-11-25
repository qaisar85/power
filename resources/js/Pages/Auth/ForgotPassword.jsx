import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function ForgotPassword({ status }) {
    const form = useForm({ email: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post('/forgot-password');
    };

    return (
        <GuestLayout title="Forgot Password">
            <Head title="Forgot Password" />

            {status && <div className="mb-4 text-sm text-green-600">{status}</div>}

            <form onSubmit={submit}>
                <div>
                    <label htmlFor="email" className="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        id="email"
                        type="email"
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    />
                    {form.errors.email && (
                        <div className="mt-2 text-sm text-red-600">{form.errors.email}</div>
                    )}
                </div>

                <div className="mt-6">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none"
                    >
                        Send reset link
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}