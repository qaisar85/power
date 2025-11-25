import React from 'react';
import { Link, useForm } from '@inertiajs/react';

export default function RolesIndex({ guard, roles, permissions, allowedPermissions }) {
  const can = (perm) => allowedPermissions?.includes(perm);
  const { data, setData, post, processing } = useForm({ name: '', guard_name: guard || 'admin' });

  const switchGuard = (g) => {
    window.location.href = route('admin.roles.index', { guard: g });
  };

  const submitNewRole = (e) => {
    e.preventDefault();
    post(route('admin.roles.store'));
  };

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Roles & Permissions</h1>
        <div className="flex gap-2">
          <button
            className={`px-3 py-1 border rounded ${guard === 'admin' ? 'bg-gray-200' : ''}`}
            onClick={() => switchGuard('admin')}
          >
            Admin Guard
          </button>
          <button
            className={`px-3 py-1 border rounded ${guard === 'web' ? 'bg-gray-200' : ''}`}
            onClick={() => switchGuard('web')}
          >
            Web Guard
          </button>
        </div>
      </div>

      {!can('roles') && (
        <div className="text-red-600">You do not have permission to manage roles.</div>
      )}

      <form onSubmit={submitNewRole} className="flex items-end gap-2">
        <div>
          <label className="block text-sm">Role name</label>
          <input
            value={data.name}
            onChange={(e) => setData('name', e.target.value)}
            className="border rounded px-3 py-2"
            placeholder="e.g., finance_admin"
          />
        </div>
        <div>
          <label className="block text-sm">Guard</label>
          <select
            value={data.guard_name}
            onChange={(e) => setData('guard_name', e.target.value)}
            className="border rounded px-3 py-2"
          >
            <option value="admin">admin</option>
            <option value="web">web</option>
          </select>
        </div>
        <button disabled={processing} className="bg-indigo-600 text-white px-4 py-2 rounded">Create Role</button>
      </form>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h2 className="text-lg font-medium mb-2">Roles ({guard})</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full border">
              <thead>
                <tr className="bg-gray-50">
                  <th className="p-2 text-left">Name</th>
                  <th className="p-2 text-left">Permissions</th>
                  <th className="p-2 text-left">Actions</th>
                </tr>
              </thead>
              <tbody>
                {roles?.map((r) => (
                  <tr key={r.id} className="border-t">
                    <td className="p-2">{r.name}</td>
                    <td className="p-2">{r.permissions?.join(', ') || '-'}</td>
                    <td className="p-2">
                      <Link href={route('admin.roles.edit', r.id)} className="px-3 py-1 bg-blue-600 text-white rounded mr-2">Edit</Link>
                      <DeleteButton roleId={r.id} />
                    </td>
                  </tr>
                ))}
                {!roles?.length && (
                  <tr>
                    <td className="p-3" colSpan={3}>No roles for guard '{guard}'.</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
        <div>
          <h2 className="text-lg font-medium mb-2">Available Permissions ({guard})</h2>
          <ul className="list-disc pl-5 text-sm text-gray-700">
            {permissions?.map((p) => (
              <li key={p.id}>{p.name}</li>
            ))}
            {!permissions?.length && (<li>No permissions for this guard.</li>)}
          </ul>
        </div>
      </div>
    </div>
  );
}

function DeleteButton({ roleId }) {
  const { delete: destroy } = useForm();
  const onDelete = () => {
    if (!confirm('Delete this role?')) return;
    destroy(route('admin.roles.destroy', roleId));
  };
  return (
    <button onClick={onDelete} className="px-3 py-1 bg-red-600 text-white rounded">Delete</button>
  );
}