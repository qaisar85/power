import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function ResetPassword({ email, token }) {
    const form = useForm({
        token: token || '',
        email: email || '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        form.post('/reset-password');
    };

    return (
        <GuestLayout title="Reset Password">
            <Head title="Reset Password" />

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

                <div className="mt-4">
                    <label htmlFor="password" className="block text-sm font-medium text-gray-700">New Password</label>
                    <input
                        id="password"
                        type="password"
                        value={form.data.password}
                        onChange={(e) => form.setData('password', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    />
                    {form.errors.password && (
                        <div className="mt-2 text-sm text-red-600">{form.errors.password}</div>
                    )}
                </div>

                <div className="mt-4">
                    <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        value={form.data.password_confirmation}
                        onChange={(e) => form.setData('password_confirmation', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                    />
                </div>

                <div className="mt-6">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none"
                    >
                        Reset password
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}