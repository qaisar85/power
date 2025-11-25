import React from 'react';
import { Link, useForm } from '@inertiajs/react';

export default function EditRoles({ user, availableRoles, allowedPermissions }) {
  const can = (perm) => allowedPermissions?.includes(perm);
  const { data, setData, patch, processing } = useForm({
    roles: user.roles || [],
  });

  const toggleRole = (roleName) => {
    const exists = data.roles.includes(roleName);
    if (exists) {
      setData('roles', data.roles.filter((r) => r !== roleName));
    } else {
      setData('roles', [...data.roles, roleName]);
    }
  };

  const submit = (e) => {
    e.preventDefault();
    patch(route('admin.users.roles.update', user.id));
  };

  return (
    <div className="p-6 space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Edit Roles: {user.name}</h1>
        <Link href={route('admin.users.index')} className="text-blue-600">Back to Users</Link>
      </div>

      {!can('roles') && (
        <div className="text-red-600">You do not have permission to manage roles.</div>
      )}

      <form onSubmit={submit} className="space-y-3">
        <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
          {availableRoles?.map((r) => (
            <label key={r.id} className="inline-flex items-center gap-2 border rounded px-3 py-2">
              <input
                type="checkbox"
                checked={data.roles.includes(r.name)}
                onChange={() => toggleRole(r.name)}
              />
              <span>{r.name}</span>
              <span className="text-xs text-gray-500">({r.guard_name})</span>
            </label>
          ))}
          {!availableRoles?.length && <div>No roles available on guard 'web'.</div>}
        </div>

        <button
          disabled={processing}
          className="bg-indigo-600 text-white px-4 py-2 rounded"
        >
          Save Roles
        </button>
      </form>
    </div>
  );
}