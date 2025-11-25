<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use App\Services\AdminActionLogger;

class AdminPermissionController extends Controller
{
    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin && $admin->can('roles'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'guard_name' => ['required', 'in:admin,web'],
        ]);

        $perm = Permission::firstOrCreate([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);

        AdminActionLogger::log($admin, 'permission.create', [
            'permission_id' => $perm->id,
            'name' => $perm->name,
            'guard_name' => $perm->guard_name,
        ]);

        return redirect()->back()->with('success', 'Permission created');
    }
}