<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleSelectionController extends Controller
{
    /**
     * Show role selection page.
     */
    public function show(): Response
    {
        $user = Auth::user();

        // If role already selected, redirect to dashboard
        if ($user->role_selected) {
            return redirect()->route('dashboard');
        }

        // Get available roles (excluding admin roles)
        $roles = Role::where('guard_name', 'web')
            ->whereNotIn('name', ['admin', 'super_admin'])
            ->orderBy('name')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $this->getRoleDescription($role->name),
                ];
            });

        return Inertia::render('Auth/RoleSelection', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store selected role.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $role = Role::where('name', $request->role)
            ->where('guard_name', 'web')
            ->firstOrFail();

        // Remove existing roles (except admin roles)
        $user->roles()->where('guard_name', 'web')
            ->whereNotIn('name', ['admin', 'super_admin'])
            ->each(function ($r) use ($user) {
                $user->removeRole($r);
            });

        // Assign new role
        $user->assignRole($role);
        $user->primary_role = $role->name;
        $user->role_selected = true;
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Role selected successfully!');
    }

    /**
     * Get role description.
     */
    protected function getRoleDescription(string $roleName): string
    {
        return match ($roleName) {
            'Company' => 'Sell or rent equipment, manage listings, participate in auctions',
            'Drilling Company' => 'Offer drilling services, manage rigs, showcase projects',
            'Inspection Company' => 'Provide inspection and repair services for equipment',
            'Logistics Company' => 'Offer transportation and shipping services',
            'Journalist' => 'Publish news articles and industry updates',
            'Branch' => 'Regional manager providing local services and support',
            'Investor' => 'Invest in projects and purchase shares',
            'Freelancer' => 'Offer freelance services and apply to projects',
            default => 'Access platform features based on your role',
        };
    }
}
