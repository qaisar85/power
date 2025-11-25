import React from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';

export default function UsersIndex({ filters, users, allowedPermissions }) {
  const { props } = usePage();
  const can = (perm) => allowedPermissions?.includes(perm);
  const [query, setQuery] = React.useState(filters?.q || '');
  const { delete: destroy, patch } = useForm();

  const onSearch = (e) => {
    e.preventDefault();
    const q = query.trim();
    window.location.href = route('admin.users.index', { q });
  };

  const toggleActive = (user, isActive) => {
    patch(route('admin.users.active', user.id), { data: { is_active: isActive } });
  };

  const deleteUser = (user) => {
    if (!confirm(`Delete user ${user.email}?`)) return;
    destroy(route('admin.users.destroy', user.id));
  };

  return (
    <div className="p-6 space-y-4">
      <h1 className="text-2xl font-semibold">Users</h1>

      <form onSubmit={onSearch} className="flex gap-2">
        <input
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          className="border rounded px-3 py-2 w-64"
          placeholder="Search name or email"
        />
        <button className="bg-blue-600 text-white px-4 py-2 rounded">Search</button>
      </form>

      {!can('users') && (
        <div className="text-red-600">You do not have permission to manage users.</div>
      )}

      <div className="overflow-x-auto">
        <table className="min-w-full border">
          <thead>
            <tr className="bg-gray-50">
              <th className="p-2 text-left">ID</th>
              <th className="p-2 text-left">Name</th>
              <th className="p-2 text-left">Email</th>
              <th className="p-2 text-left">Type</th>
              <th className="p-2 text-left">Roles</th>
              <th className="p-2 text-left">Active</th>
              <th className="p-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {users?.data?.map((u) => (
              <tr key={u.id} className="border-t">
                <td className="p-2">{u.id}</td>
                <td className="p-2">{u.name}</td>
                <td className="p-2">{u.email}</td>
                <td className="p-2">{u.user_type}</td>
                <td className="p-2">{u.roles?.join(', ') || '-'}</td>
                <td className="p-2">
                  <label className="inline-flex items-center gap-2">
                    <input
                      type="checkbox"
                      checked={u.is_active}
                      onChange={(e) => toggleActive(u, e.target.checked)}
                    />
                    <span>{u.is_active ? 'Active' : 'Inactive'}</span>
                  </label>
                </td>
                <td className="p-2 flex gap-2">
                  <Link
                    href={route('admin.users.roles', u.id)}
                    className="px-3 py-1 bg-indigo-600 text-white rounded"
                  >
                    Edit Roles
                  </Link>
                  <button
                    onClick={() => deleteUser(u)}
                    className="px-3 py-1 bg-red-600 text-white rounded"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            ))}
            {!users?.data?.length && (
              <tr>
                <td className="p-3" colSpan={7}>No users found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      <div className="flex items-center gap-2">
        {users?.links?.map((l, idx) => (
          <Link
            key={idx}
            href={l.url || '#'}
            className={`px-2 py-1 border rounded ${l.active ? 'bg-gray-200' : ''}`}
            dangerouslySetInnerHTML={{ __html: l.label }}
          />
        ))}
      </div>
    </div>
  );
}