import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';

export default function RoleSelection({ roles }) {
    const form = useForm({
        role: '',
    });

    const [selectedRole, setSelectedRole] = useState(null);

    const submit = (e) => {
        e.preventDefault();
        if (!form.data.role) {
            return;
        }
        form.post('/role-selection');
    };

    const handleRoleSelect = (roleName) => {
        setSelectedRole(roleName);
        form.setData('role', roleName);
    };

    const roleIcons = {
        'Company': '🏢',
        'Drilling Company': '⛏️',
        'Inspection Company': '🔍',
        'Logistics Company': '🚚',
        'Journalist': '📰',
        'Branch': '🌍',
        'Investor': '💰',
        'Freelancer': '💼',
    };

    const roleColors = {
        'Company': 'border-blue-500 bg-blue-50',
        'Drilling Company': 'border-orange-500 bg-orange-50',
        'Inspection Company': 'border-green-500 bg-green-50',
        'Logistics Company': 'border-purple-500 bg-purple-50',
        'Journalist': 'border-red-500 bg-red-50',
        'Branch': 'border-indigo-500 bg-indigo-50',
        'Investor': 'border-yellow-500 bg-yellow-50',
        'Freelancer': 'border-pink-500 bg-pink-50',
    };

    return (
        <GuestLayout title="Select Your Role">
            <Head title="Select Your Role" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-gray-900 mb-2">Welcome to World Oil Portal!</h2>
                <p className="text-gray-600">
                    Please select your primary role to continue. This helps us customize your experience.
                </p>
            </div>

            {form.errors.role && (
                <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-600">
                    {form.errors.role}
                </div>
            )}

            <form onSubmit={submit}>
                <div className="space-y-3">
                    {roles.map((role) => {
                        const isSelected = selectedRole === role.name;
                        const borderColor = roleColors[role.name] || 'border-gray-300 bg-gray-50';
                        
                        return (
                            <label
                                key={role.id}
                                className={`block p-4 border-2 rounded-lg cursor-pointer transition-all ${
                                    isSelected
                                        ? `${borderColor} ring-2 ring-offset-2 ring-indigo-500`
                                        : 'border-gray-300 bg-white hover:border-indigo-300 hover:bg-indigo-50'
                                }`}
                            >
                                <div className="flex items-start">
                                    <input
                                        type="radio"
                                        name="role"
                                        value={role.name}
                                        checked={isSelected}
                                        onChange={() => handleRoleSelect(role.name)}
                                        className="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                    />
                                    <div className="ml-3 flex-1">
                                        <div className="flex items-center">
                                            <span className="text-2xl mr-2">{roleIcons[role.name] || '👤'}</span>
                                            <span className="text-lg font-semibold text-gray-900">
                                                {role.name}
                                            </span>
                                        </div>
                                        {role.description && (
                                            <p className="mt-1 text-sm text-gray-600">
                                                {role.description}
                                            </p>
                                        )}
                                    </div>
                                    {isSelected && (
                                        <svg
                                            className="h-5 w-5 text-indigo-600"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    )}
                                </div>
                            </label>
                        );
                    })}
                </div>

                <div className="mt-6">
                    <button
                        type="submit"
                        disabled={form.processing || !selectedRole}
                        className="w-full inline-flex justify-center items-center px-4 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {form.processing ? (
                            <>
                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </>
                        ) : (
                            'Continue to Dashboard'
                        )}
                    </button>
                </div>

                <div className="mt-4 text-center">
                    <p className="text-xs text-gray-500">
                        You can change your role later from your account settings.
                    </p>
                </div>
            </form>
        </GuestLayout>
    );
}

