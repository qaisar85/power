<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BusinessSectorSeeder::class,
            ModuleSeeder::class,
            PaymentMethodsSeeder::class,
            RoleSeeder::class,
            // Register admin bootstrap
            AdminSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@globalbiz.com',
            'user_type' => 'admin',
        ]);

        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user_type' => 'business',
        ]);

        // Give users access to all modules
        $modules = \App\Models\Module::all();
        $admin->modules()->attach($modules->pluck('id'));
        $user->modules()->attach($modules->pluck('id'));
    }
}
