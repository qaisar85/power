<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use App\Services\AdminActionLogger;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('users'), 403);

        $search = $request->string('q')->toString();
        $users = User::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->through(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'user_type' => $u->user_type,
                    'is_active' => (bool) $u->is_active,
                    'roles' => $u->getRoleNames()->toArray(),
                    'created_at' => $u->created_at?->toDateTimeString(),
                ];
            })
            ->appends(['q' => $search]);

        return Inertia::render('Admin/Users/Index', [
            'filters' => [
                'q' => $search,
            ],
            'users' => $users,
            'allowedPermissions' => $admin->getAllPermissions()->pluck('name'),
        ]);
    }

    public function showRoles(User $user)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && ($admin->can('users') || $admin->can('roles')), 403);

        $availableRoles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name', 'guard_name']);

        return Inertia::render('Admin/Users/EditRoles', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->toArray(),
            ],
            'availableRoles' => $availableRoles,
            'allowedPermissions' => $admin->getAllPermissions()->pluck('name'),
        ]);
    }

    public function updateRoles(Request $request, User $user)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('roles'), 403);

        $roles = collect($request->input('roles', []))
            ->filter()
            ->unique()
            ->values();

        // Ensure roles exist for the web guard
        $roles->each(function ($roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        });

        $previous = $user->getRoleNames()->toArray();
        $user->syncRoles($roles->toArray());

        AdminActionLogger::log(
            $admin,
            'user.roles.sync',
            [
                'user_id' => $user->id,
                'previous_roles' => $previous,
                'new_roles' => $roles->toArray(),
            ]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Roles updated successfully');
    }

    public function toggleActive(Request $request, User $user)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('users'), 403);

        $setActive = (bool) $request->boolean('is_active', true);
        $user->is_active = $setActive;
        $user->save();

        AdminActionLogger::log(
            $admin,
            $setActive ? 'user.activate' : 'user.deactivate',
            [
                'user_id' => $user->id,
                'is_active' => $setActive,
            ]
        );

        return redirect()
            ->back()
            ->with('success', 'User status updated');
    }

    public function destroy(User $user)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && ($admin->can('users') || $admin->can('moderation')), 403);

        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
        ];

        $user->delete();

        AdminActionLogger::log($admin, 'user.delete', $payload);

        return redirect()
            ->back()
            ->with('success', 'User deleted');
    }
}