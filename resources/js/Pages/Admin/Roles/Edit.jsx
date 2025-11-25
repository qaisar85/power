import React from 'react';
import { Link, useForm } from '@inertiajs/react';

export default function RoleEdit({ role, availablePermissions, allowedPermissions }) {
  const can = (perm) => allowedPermissions?.includes(perm);
  const { data, setData, patch, processing } = useForm({ permissions: role.permissions || [] });
  const { post } = useForm({ name: '', guard_name: role.guard_name });

  const togglePermission = (permName) => {
    const exists = data.permissions.includes(permName);
    setData('permissions', exists ? data.permissions.filter((p) => p !== permName) : [...data.permissions, permName]);
  };

  const submit = (e) => {
    e.preventDefault();
    patch(route('admin.roles.update', role.id));
  };

  const addPermission = (e) => {
    e.preventDefault();
    const name = prompt('Enter new permission name:');
    if (!name) return;
    post(route('admin.permissions.store'), {
      data: { name, guard_name: role.guard_name },
      onSuccess: () => window.location.reload(),
    });
  };

  return (
    <div className="p-6 space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Edit Role: {role.name} ({role.guard_name})</h1>
        <Link href={route('admin.roles.index', { guard: role.guard_name })} className="text-blue-600">Back to Roles</Link>
      </div>

      {!can('roles') && (
        <div className="text-red-600">You do not have permission to manage roles.</div>
      )}

      <form onSubmit={submit} className="space-y-3">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-medium">Assign Permissions</h2>
          <button onClick={addPermission} className="px-3 py-1 bg-green-600 text-white rounded">Add Permission</button>
        </div>
        <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
          {availablePermissions?.map((p) => (
            <label key={p.id} className="inline-flex items-center gap-2 border rounded px-3 py-2">
              <input
                type="checkbox"
                checked={data.permissions.includes(p.name)}
                onChange={() => togglePermission(p.name)}
              />
              <span>{p.name}</span>
              <span className="text-xs text-gray-500">({p.guard_name})</span>
            </label>
          ))}
          {!availablePermissions?.length && <div>No permissions on this guard.</div>}
        </div>

        <button
          disabled={processing}
          className="bg-indigo-600 text-white px-4 py-2 rounded"
        >
          Save Changes
        </button>
      </form>
    </div>
  );
}