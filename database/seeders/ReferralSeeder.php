<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferralSeeder extends Seeder
{
    public function run()
    {
        // Create referral configuration table if needed
        DB::table('referral_configs')->insert([
            [
                'level' => 1,
                'commission_rate' => 10.00,
                'description' => 'Direct referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 2,
                'commission_rate' => 5.00,
                'description' => 'Level 2 referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 3,
                'commission_rate' => 3.00,
                'description' => 'Level 3 referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 4,
                'commission_rate' => 2.00,
                'description' => 'Level 4 referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 5,
                'commission_rate' => 1.50,
                'description' => 'Level 5 referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 6,
                'commission_rate' => 1.00,
                'description' => 'Level 6 referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 7,
                'commission_rate' => 0.75,
                'description' => 'Level 7 referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'level' => 8,
                'commission_rate' => 0.50,
                'description' => 'Level 8 referral',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}