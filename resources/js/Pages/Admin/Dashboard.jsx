import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function Dashboard({ stats = {}, allowedPermissions = [] }) {
  const can = (permNames) => {
    if (!Array.isArray(permNames)) permNames = [permNames];
    return permNames.some((p) => allowedPermissions.includes(p));
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title="Super Admin Dashboard" />
      <div className="mx-auto max-w-7xl px-4 py-8">
        <div className="flex items-center justify-between mb-6">
          <h1 className="text-2xl font-semibold">Super Admin Dashboard</h1>
          <form method="post" action={route('admin.logout')}>
            <input type="hidden" name="_token" value={document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? ''} />
            <button type="submit" className="px-3 py-2 text-sm bg-gray-800 text-white rounded hover:bg-black">
              Logout
            </button>
          </form>
        </div>

        <section className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
          <div className="bg-white shadow rounded p-4">
            <div className="text-sm text-gray-500">Users</div>
            <div className="text-2xl font-bold">{stats.users ?? 0}</div>
          </div>
          <div className="bg-white shadow rounded p-4">
            <div className="text-sm text-gray-500">Revenue</div>
            <div className="text-2xl font-bold">{stats.revenue ?? 0}</div>
          </div>
          <div className="bg-white shadow rounded p-4">
            <div className="text-sm text-gray-500">Activity</div>
            <div className="text-2xl font-bold">{stats.activity ?? 0}</div>
          </div>
        </section>

        <section className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {can(['users']) && (
            <Card title="User Management">
              <ul className="text-sm text-gray-600 list-disc pl-5">
                <li>Create/edit users, companies</li>
                <li>Assign roles and permissions</li>
                <li>View history and actions</li>
                <li>
                  <Link href={route('admin.users.index')} className="text-indigo-600 hover:text-indigo-800">Open Users</Link>
                </li>
              </ul>
            </Card>
          )}
          {can(['roles']) && (
            <Card title="Roles & Permissions">
              <ul className="text-sm text-gray-600 list-disc pl-5">
                <li>Create new roles (e.g., Recruitment Agent)</li>
                <li>Edit role permissions</li>
                <li>Configure dashboards and functions</li>
                <li>
                  <Link href={route('admin.roles.index', { guard: 'admin' })} className="text-indigo-600 hover:text-indigo-800">Open Roles</Link>
                </li>
              </ul>
            </Card>
          )}
          {can(['moderation']) && (
            <Card title="Moderation & Administration">
              <ul className="text-sm text-gray-600 list-disc pl-5">
                <li>Approve user proposals and documents</li>
                <li>Manage blocks, translations, news</li>
                <li>Assign moderators to sections</li>
                <li>
                  <Link href={route('admin.moderation.index')} className="text-indigo-600 hover:text-indigo-800">Open Moderation</Link>
                </li>
              </ul>
            </Card>
          )}
          {can(['finance']) && (
            <Card title="Finance">
              <ul className="text-sm text-gray-600 list-disc pl-5">
                <li>Balances and payment history</li>
                <li>Configure options, tariffs, packages</li>
                <li>Set prices and limits</li>
                <li>
                  <Link href={route('admin.finance.index')} className="text-indigo-600 hover:text-indigo-800">Open Finance</Link>
                </li>
              </ul>
            </Card>
          )}
          {can(['modules', 'options', 'tariffs']) && (
            <Card title="Modules & Options">
              <ul className="text-sm text-gray-600 list-disc pl-5">
                <li>Create new modules and interfaces</li>
                <li>Add/remove dashboard elements</li>
                <li>Feature management</li>
              </ul>
            </Card>
          )}
          {can(['dashboard', 'content', 'translations', 'news']) && (
            <Card title="Content & Config">
              <ul className="text-sm text-gray-600 list-disc pl-5">
                <li>Translations and content sections</li>
                <li>News and announcements</li>
                <li>Dashboard configuration</li>
              </ul>
            </Card>
          )}
          <Card title="Action Log">
            <ul className="text-sm text-gray-600 list-disc pl-5">
              <li>
                View journal of actions: who, when, what —{' '}
                <Link href={route('admin.logs.index')} className="text-indigo-600 hover:text-indigo-800">Open Logs</Link>
              </li>
              <li>Filter by admin, section, and time</li>
              <li>Export for audit (coming soon)</li>
            </ul>
          </Card>
        </section>
      </div>
    </div>
  );
}

function Card({ title, children }) {
  return (
    <div className="bg-white shadow rounded p-4">
      <h2 className="text-lg font-medium mb-2">{title}</h2>
      {children}
    </div>
  );
}