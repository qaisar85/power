import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Edit({ mustVerifyEmail, status }) {
    const { props } = usePage();
    const user = props?.auth?.user ?? { name: '', email: '' };

    const profileForm = useForm({
        name: user.name || '',
        email: user.email || '',
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const deleteForm = useForm({
        password: '',
    });

    const [verificationMessage, setVerificationMessage] = useState(null);

    const submitProfile = (e) => {
        e.preventDefault();
        profileForm.patch('/profile');
    };

    const submitPassword = (e) => {
        e.preventDefault();
        passwordForm.put('/password', {
            onSuccess: () => passwordForm.reset('current_password', 'password', 'password_confirmation'),
        });
    };

    const submitDelete = (e) => {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            return;
        }
        deleteForm.delete('/profile');
    };

    const resendVerification = () => {
        fetch('/email/verification-notification', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
            .then(() => setVerificationMessage('Verification link sent.'))
            .catch(() => setVerificationMessage('Failed to send verification link.'));
    };

    return (
        <AppLayout title="Profile">
            <Head title="Profile" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
                    {/* Header */}
                    <div className="px-4 sm:px-0">
                        <h2 className="text-lg font-medium text-gray-900">Profile</h2>
                        <p className="mt-1 text-sm text-gray-600">Manage your account settings and preferences.</p>
                    </div>

                    {/* Email Verification Notice */}
                    {mustVerifyEmail && (
                        <div className="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded">
                            <div className="flex items-center justify-between">
                                <p>
                                    Your email address is unverified. Please verify your email.
                                    {status === 'verification-link-sent' && (
                                        <span className="ml-2 text-green-700">A new verification link has been sent.</span>
                                    )}
                                </p>
                                <button
                                    type="button"
                                    onClick={resendVerification}
                                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700"
                                >
                                    Resend Verification Email
                                </button>
                            </div>
                            {verificationMessage && (
                                <p className="mt-2 text-sm">{verificationMessage}</p>
                            )}
                        </div>
                    )}

                    {/* Update Profile Information */}
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900">Update Profile Information</h3>
                        <form onSubmit={submitProfile} className="mt-6 space-y-6">
                            <div>
                                <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                    Name
                                </label>
                                <input
                                    id="name"
                                    type="text"
                                    value={profileForm.data.name}
                                    onChange={(e) => profileForm.setData('name', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {profileForm.errors.name && (
                                    <p className="mt-2 text-sm text-red-600">{profileForm.errors.name}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    value={profileForm.data.email}
                                    onChange={(e) => profileForm.setData('email', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {profileForm.errors.email && (
                                    <p className="mt-2 text-sm text-red-600">{profileForm.errors.email}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-3">
                                <button
                                    type="submit"
                                    disabled={profileForm.processing}
                                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Update Password */}
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900">Update Password</h3>
                        <form onSubmit={submitPassword} className="mt-6 space-y-6">
                            <div>
                                <label htmlFor="current_password" className="block text-sm font-medium text-gray-700">
                                    Current Password
                                </label>
                                <input
                                    id="current_password"
                                    type="password"
                                    value={passwordForm.data.current_password}
                                    onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {passwordForm.errors.current_password && (
                                    <p className="mt-2 text-sm text-red-600">{passwordForm.errors.current_password}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="password" className="block text_sm font-medium text-gray-700">
                                    New Password
                                </label>
                                <input
                                    id="password"
                                    type="password"
                                    value={passwordForm.data.password}
                                    onChange={(e) => passwordForm.setData('password', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {passwordForm.errors.password && (
                                    <p className="mt-2 text-sm text-red-600">{passwordForm.errors.password}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
                                    Confirm Password
                                </label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    value={passwordForm.data.password_confirmation}
                                    onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                                    className="mt-1 block w_full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {passwordForm.errors.password_confirmation && (
                                    <p className="mt-2 text-sm text-red-600">{passwordForm.errors.password_confirmation}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-3">
                                <button
                                    type="submit"
                                    disabled={passwordForm.processing}
                                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Delete Account */}
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900">Delete Account</h3>
                        <p className="mt-1 text-sm text-gray-600">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                        <form onSubmit={submitDelete} className="mt-6 space-y-6">
                            <div>
                                <label htmlFor="delete_password" className="block text-sm font-medium text-gray-700">
                                    Password
                                </label>
                                <input
                                    id="delete_password"
                                    type="password"
                                    value={deleteForm.data.password}
                                    onChange={(e) => deleteForm.setData('password', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {deleteForm.errors.password && (
                                    <p className="mt-2 text-sm text-red-600">{deleteForm.errors.password}</p>
                                )}
                            </div>

                            <button
                                type="submit"
                                disabled={deleteForm.processing}
                                className="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                            >
                                Delete Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}