<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users', 'moderation', 'finance', 'content', 'dashboard', 'roles', 'categories', 'news', 'translations', 'modules', 'options', 'tariffs'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'admin']
            );
        }

        $superRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $financeRole = Role::firstOrCreate(['name' => 'finance_admin', 'guard_name' => 'admin']);

        $superRole->syncPermissions(Permission::where('guard_name', 'admin')->get());

        $adminUser = Admin::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('ChangeMe123!'),
                'role' => 'super_admin',
                'permissions' => $permissions,
                'country' => null,
                'region' => null,
            ]
        );

        if (!$adminUser->hasRole('super_admin')) {
            $adminUser->assignRole('super_admin');
        }
    }
}