import React from 'react';
import { Head, useForm } from '@inertiajs/react';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: true,
  });

  const submit = (e) => {
    e.preventDefault();
    post(route('admin.login.attempt'));
  };

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-6">
      <Head title="Admin Login" />
      <div className="w-full max-w-md bg-white shadow rounded-lg p-6">
        <h1 className="text-2xl font-semibold text-gray-800 mb-4">Admin Login</h1>
        <p className="text-sm text-gray-500 mb-6">Sign in with your administrator credentials.</p>
        <form onSubmit={submit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Email</label>
            <input
              type="email"
              value={data.email}
              onChange={(e) => setData('email', e.target.value)}
              className="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="admin@example.com"
              required
            />
            {errors.email && (
              <div className="mt-1 text-sm text-red-600">{errors.email}</div>
            )}
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Password</label>
            <input
              type="password"
              value={data.password}
              onChange={(e) => setData('password', e.target.value)}
              className="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="••••••••"
              required
            />
            {errors.password && (
              <div className="mt-1 text-sm text-red-600">{errors.password}</div>
            )}
          </div>
          <div className="flex items-center justify-between">
            <label className="flex items-center space-x-2 text-sm text-gray-700">
              <input
                type="checkbox"
                checked={data.remember}
                onChange={(e) => setData('remember', e.target.checked)}
              />
              <span>Remember me</span>
            </label>
            <button
              type="submit"
              disabled={processing}
              className="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
            >
              {processing ? 'Signing in…' : 'Sign in'}
            </button>
          </div>
          {errors.message && (
            <div className="mt-2 text-sm text-red-600">{errors.message}</div>
          )}
        </form>
      </div>
    </div>
  );
}