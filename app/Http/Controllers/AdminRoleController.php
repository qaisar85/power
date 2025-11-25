<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Services\AdminActionLogger;

class AdminRoleController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('roles'), 403);

        $guard = $request->string('guard')->toString() ?: 'admin';
        if (!in_array($guard, ['admin', 'web'])) {
            $guard = 'admin';
        }

        $roles = Role::query()
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'guard_name' => $r->guard_name,
                    'permissions' => $r->permissions->pluck('name')->values()->all(),
                ];
            });

        $permissions = Permission::query()
            ->where('guard_name', $guard)
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return Inertia::render('Admin/Roles/Index', [
            'guard' => $guard,
            'roles' => $roles,
            'permissions' => $permissions,
            'allowedPermissions' => $admin->getAllPermissions()->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('roles'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'guard_name' => ['required', 'in:admin,web'],
        ]);

        $role = Role::firstOrCreate([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);

        AdminActionLogger::log($admin, 'role.create', [
            'role_id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
        ]);

        return redirect()->route('admin.roles.index', ['guard' => $role->guard_name])
            ->with('success', 'Role created');
    }

    public function edit(Role $role)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('roles'), 403);

        $permissions = Permission::query()
            ->where('guard_name', $role->guard_name)
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
            ],
            'availablePermissions' => $permissions,
            'allowedPermissions' => $admin->getAllPermissions()->pluck('name'),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('roles'), 403);

        $data = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $newPerms = collect($data['permissions'] ?? [])->filter()->unique()->values();

        // Ensure all permissions exist for the role guard
        $newPerms->each(function ($permName) use ($role) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => $role->guard_name]);
        });

        $previous = $role->permissions->pluck('name')->values()->all();
        $role->syncPermissions($newPerms->toArray());

        AdminActionLogger::log($admin, 'role.permissions.sync', [
            'role_id' => $role->id,
            'previous_permissions' => $previous,
            'new_permissions' => $newPerms->toArray(),
        ]);

        return redirect()->route('admin.roles.edit', $role->id)->with('success', 'Role permissions updated');
    }

    public function destroy(Role $role)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('roles'), 403);

        $payload = [
            'role_id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
        ];
        $guard = $role->guard_name;
        $role->delete();

        AdminActionLogger::log($admin, 'role.delete', $payload);

        return redirect()->route('admin.roles.index', ['guard' => $guard])->with('success', 'Role deleted');
    }
}