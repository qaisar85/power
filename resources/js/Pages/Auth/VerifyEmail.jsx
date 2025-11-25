import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function VerifyEmail({ status }) {
    const [message, setMessage] = useState(null);

    const resend = async () => {
        try {
            await fetch('/email/verification-notification', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            });
            setMessage('Verification link sent.');
        } catch (e) {
            setMessage('Failed to send verification link.');
        }
    };

    return (
        <GuestLayout title="Verify Email">
            <Head title="Email Verification" />

            <div className="mb-4 text-sm text-gray-600">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </div>

            {status === 'verification-link-sent' && (
                <div className="mb-4 font-medium text-sm text-green-600">
                    A new verification link has been sent to your email address.
                </div>
            )}

            {message && (
                <div className="mb-4 font-medium text-sm text-indigo-600">{message}</div>
            )}

            <div className="mt-4 flex items-center justify-between">
                <button
                    type="button"
                    onClick={resend}
                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700"
                >
                    Resend Verification Email
                </button>

                <div>
                    <Link href="/profile" className="underline text-sm text-gray-600 hover:text-gray-900">
                        Edit Profile
                    </Link>
                    <Link href="/logout" method="post" as="button" className="ml-3 underline text-sm text-gray-600 hover:text-gray-900">
                        Log Out
                    </Link>
                </div>
            </div>
        </GuestLayout>
    );
}