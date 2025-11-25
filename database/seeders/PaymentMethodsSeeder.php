<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Card (Stripe)',
                'type' => 'card',
                'is_active' => true,
                'config' => [
                    'provider' => 'stripe',
                    'mode' => 'test',
                ],
                'sort_order' => 10,
            ],
            [
                'name' => 'Bank Payment',
                'type' => 'bank',
                'is_active' => true,
                'config' => [
                    'provider' => 'bank',
                ],
                'sort_order' => 20,
            ],
            [
                'name' => 'Crypto',
                'type' => 'crypto',
                'is_active' => false,
                'config' => [
                    'provider' => 'coinbase',
                ],
                'sort_order' => 30,
            ],
            [
                'name' => 'Virtual Balance',
                'type' => 'virtual',
                'is_active' => true,
                'config' => [],
                'sort_order' => 40,
            ],
            [
                'name' => 'PayPal',
                'type' => 'paypal',
                'is_active' => true,
                'config' => [
                    'provider' => 'paypal',
                    'mode' => 'sandbox',
                    'client_id' => null,
                    'client_secret' => null,
                ],
                'sort_order' => 50,
            ],
            // New providers appended after existing items
            [
                'name' => 'Razorpay',
                'type' => 'razorpay',
                'is_active' => true,
                'config' => [
                    'provider' => 'razorpay',
                    'mode' => 'test',
                    'key_id' => null,
                    'key_secret' => null,
                ],
                'sort_order' => 80,
            ],
            [
                'name' => 'Flutterwave',
                'type' => 'flutterwave',
                'is_active' => true,
                'config' => [
                    'provider' => 'flutterwave',
                    'mode' => 'test',
                    'public_key' => null,
                    'secret_key' => null,
                ],
                'sort_order' => 85,
            ],
            [
                'name' => 'Mollie',
                'type' => 'mollie',
                'is_active' => true,
                'config' => [
                    'provider' => 'mollie',
                    'mode' => 'test',
                    'api_key' => null,
                ],
                'sort_order' => 90,
            ],
            [
                'name' => 'Paystack',
                'type' => 'paystack',
                'is_active' => true,
                'config' => [
                    'provider' => 'paystack',
                    'mode' => 'test',
                    'secret_key' => null,
                ],
                'sort_order' => 95,
            ],
            [
                'name' => 'Instamojo',
                'type' => 'instamojo',
                'is_active' => true,
                'config' => [
                    'provider' => 'instamojo',
                    'mode' => 'test',
                    'api_key' => null,
                    'auth_token' => null,
                ],
                'sort_order' => 100,
            ],
            [
                'name' => 'Apple Pay (Stripe)',
                'type' => 'apple_pay',
                'is_active' => false,
                'config' => [
                    'provider' => 'stripe',
                    'mode' => 'test',
                    'apple_merchant_id' => null,
                ],
                'sort_order' => 60,
            ],
            [
                'name' => 'Google Pay (Stripe)',
                'type' => 'google_pay',
                'is_active' => false,
                'config' => [
                    'provider' => 'stripe',
                    'mode' => 'test',
                    'merchant_id' => null,
                ],
                'sort_order' => 70,
            ],
        ];

        foreach ($methods as $m) {
            PaymentMethod::updateOrCreate(
                ['type' => $m['type']],
                [
                    'name' => $m['name'],
                    'is_active' => $m['is_active'],
                    'config' => $m['config'],
                    'sort_order' => $m['sort_order'],
                ]
            );
        }
    }
}
