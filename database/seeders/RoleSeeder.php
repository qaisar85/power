<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Company',
                'guard_name' => 'web',
                'description' => 'Equipment seller/renter - Create listings, manage products, participate in auctions',
            ],
            [
                'name' => 'Drilling Company',
                'guard_name' => 'web',
                'description' => 'Offer drilling services, manage rigs, showcase projects',
            ],
            [
                'name' => 'Inspection Company',
                'guard_name' => 'web',
                'description' => 'Provide inspection and repair services for equipment',
            ],
            [
                'name' => 'Logistics Company',
                'guard_name' => 'web',
                'description' => 'Offer transportation and shipping services',
            ],
            [
                'name' => 'Journalist',
                'guard_name' => 'web',
                'description' => 'Publish news articles and industry updates',
            ],
            [
                'name' => 'Branch',
                'guard_name' => 'web',
                'description' => 'Regional manager providing local services and support',
            ],
            [
                'name' => 'Investor',
                'guard_name' => 'web',
                'description' => 'Invest in projects and purchase shares',
            ],
            [
                'name' => 'Freelancer',
                'guard_name' => 'web',
                'description' => 'Offer freelance services and apply to projects',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                [
                    'name' => $roleData['name'],
                    'guard_name' => $roleData['guard_name'],
                ],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}
