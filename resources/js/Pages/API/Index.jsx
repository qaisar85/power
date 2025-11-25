import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ApiTokens({ tokens = [], availablePermissions = [], defaultPermissions = [] }) {
    const createForm = useForm({ name: '', permissions: defaultPermissions || [] });

    const [editing, setEditing] = useState(null); // token id
    const editForm = useForm({ name: '', permissions: [] });

    const [localTokens, setLocalTokens] = useState(tokens);
    const [plainToken, setPlainToken] = useState(null);

    const submitCreate = (e) => {
        e.preventDefault();
        createForm.post('/user/api-tokens', {
            onSuccess: (page) => {
                // Jetstream returns the created token in props; refresh via inertia
                setPlainToken(page?.props?.token ?? null);
                createForm.setData('name', '');
                createForm.setData('permissions', defaultPermissions || []);
            },
        });
    };

    const startEdit = (token) => {
        setEditing(token.id);
        editForm.setData('name', token.name);
        editForm.setData('permissions', token.abilities || []);
    };

    const submitEdit = (e) => {
        e.preventDefault();
        if (!editing) return;
        editForm.put(`/user/api-tokens/${editing}`, {
            onSuccess: () => setEditing(null),
        });
    };

    const deleteToken = (id) => {
        if (!confirm('Delete this token?')) return;
        const form = useForm({});
        form.delete(`/user/api-tokens/${id}`);
    };

    const togglePermission = (perm, form) => {
        const perms = new Set(form.data.permissions || []);
        if (perms.has(perm)) {
            perms.delete(perm);
        } else {
            perms.add(perm);
        }
        form.setData('permissions', Array.from(perms));
    };

    return (
        <AppLayout title="API Tokens">
            <Head title="API Tokens" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-800 leading-tight">API Tokens</h2>
                    </div>

                    {/* Create Token */}
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900">Create API Token</h3>
                        <form onSubmit={submitCreate} className="mt-6 space-y-6">
                            <div>
                                <label htmlFor="token_name" className="block text-sm font-medium text-gray-700">
                                    Token Name
                                </label>
                                <input
                                    id="token_name"
                                    type="text"
                                    value={createForm.data.name}
                                    onChange={(e) => createForm.setData('name', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                {createForm.errors.name && (
                                    <p className="mt-2 text-sm text-red-600">{createForm.errors.name}</p>
                                )}
                            </div>

                            <div>
                                <span className="block text-sm font-medium text-gray-700">Permissions</span>
                                <div className="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                                    {availablePermissions.map((perm) => (
                                        <label key={perm} className="inline-flex items-center">
                                            <input
                                                type="checkbox"
                                                checked={(createForm.data.permissions || []).includes(perm)}
                                                onChange={() => togglePermission(perm, createForm)}
                                                className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            />
                                            <span className="ml-2 text-sm text-gray-700">{perm}</span>
                                        </label>
                                    ))}
                                </div>
                                {createForm.errors.permissions && (
                                    <p className="mt-2 text-sm text-red-600">{createForm.errors.permissions}</p>
                                )}
                            </div>

                            <div className="flex items-center justify-end gap-3">
                                <button
                                    type="submit"
                                    disabled={createForm.processing}
                                    className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Create
                                </button>
                            </div>

                            {plainToken && (
                                <div className="mt-6 bg-gray-50 border border-gray-200 rounded p-4">
                                    <p className="text-sm text-gray-700">
                                        Make sure to copy your new API token. You won’t see it again!
                                    </p>
                                    <pre className="mt-2 p-3 bg-white border rounded text-sm overflow-x-auto">{plainToken}</pre>
                                </div>
                            )}
                        </form>
                    </div>

                    {/* Existing Tokens */}
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900">Manage Tokens</h3>

                        <div className="mt-6 space-y-6">
                            {localTokens.length === 0 ? (
                                <p className="text-sm text-gray-600">No tokens created yet.</p>
                            ) : (
                                localTokens.map((token) => (
                                    <div key={token.id} className="border rounded p-4">
                                        {editing === token.id ? (
                                            <form onSubmit={submitEdit} className="space-y-4">
                                                <div>
                                                    <label className="block text-sm font-medium text-gray-700">Name</label>
                                                    <input
                                                        type="text"
                                                        value={editForm.data.name}
                                                        onChange={(e) => editForm.setData('name', e.target.value)}
                                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    />
                                                    {editForm.errors.name && (
                                                        <p className="mt-2 text-sm text-red-600">{editForm.errors.name}</p>
                                                    )}
                                                </div>
                                                <div>
                                                    <span className="block text-sm font-medium text-gray-700">Permissions</span>
                                                    <div className="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                                                        {availablePermissions.map((perm) => (
                                                            <label key={perm} className="inline-flex items-center">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={(editForm.data.permissions || []).includes(perm)}
                                                                    onChange={() => togglePermission(perm, editForm)}
                                                                    className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                                />
                                                                <span className="ml-2 text-sm text-gray-700">{perm}</span>
                                                            </label>
                                                        ))}
                                                    </div>
                                                </div>
                                                <div className="flex items-center justify-end gap-3">
                                                    <button
                                                        type="button"
                                                        onClick={() => setEditing(null)}
                                                        className="px-4 py-2 bg-gray-200 rounded-md text-gray-800 hover:bg-gray-300"
                                                    >
                                                        Cancel
                                                    </button>
                                                    <button
                                                        type="submit"
                                                        disabled={editForm.processing}
                                                        className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                                                    >
                                                        Save
                                                    </button>
                                                </div>
                                            </form>
                                        ) : (
                                            <div className="flex items-center justify-between">
                                                <div>
                                                    <p className="font-medium text-gray-900">{token.name}</p>
                                                    <p className="text-sm text-gray-600 mt-1">
                                                        Permissions: {(token.abilities || []).join(', ') || 'none'}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => startEdit(token)}
                                                        className="px-3 py-2 bg-white border rounded-md hover:bg-gray-50"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => deleteToken(token.id)}
                                                        className="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}