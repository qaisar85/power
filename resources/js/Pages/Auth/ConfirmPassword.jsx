import React, { useRef } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function ConfirmPassword() {
    const passwordInput = useRef(null);
    const form = useForm({ password: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post('/confirm-password', {
            onFinish: () => {
                form.reset();
                passwordInput.current?.focus();
            },
        });
    };

    return (
        <GuestLayout title="Secure Area">
            <Head title="Secure Area" />

            <div className="mb-4 text-sm text-gray-600">
                This is a secure area of the application. Please confirm your password before continuing.
            </div>

            <form onSubmit={submit}>
                <div>
                    <label htmlFor="password" className="block text-sm font-medium text-gray-700">Password</label>
                    <input
                        id="password"
                        ref={passwordInput}
                        type="password"
                        value={form.data.password}
                        onChange={(e) => form.setData('password', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        autoComplete="current-password"
                    />
                    {form.errors.password && (
                        <div className="mt-2 text-sm text-red-600">{form.errors.password}</div>
                    )}
                </div>

                <div className="mt-6 flex justify-end">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700"
                    >
                        Confirm
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}