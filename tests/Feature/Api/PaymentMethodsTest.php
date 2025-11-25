<?php

use App\Models\User;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(\Tests\TestCase::class, RefreshDatabase::class);
uses(\Illuminate\Foundation\Testing\WithFaker::class);

beforeEach(function () {
    // Ensure payment methods exist in DB for the test
    PaymentMethod::create([
        'name' => 'Card (Stripe)',
        'type' => 'card',
        'is_active' => true,
        'config' => ['provider' => 'stripe'],
        'sort_order' => 10,
    ]);

    PaymentMethod::create([
        'name' => 'PayPal',
        'type' => 'paypal',
        'is_active' => true,
        'config' => ['provider' => 'paypal'],
        'sort_order' => 50,
    ]);
});

it('returns active payment methods for authenticated user', function () {
    $user = User::factory()->create();

    $response = test()->actingAs($user, 'sanctum')->getJson('/api/v1/wallet/payment-methods');

    $response->assertStatus(200);
    $response->assertJsonCount(2);
    $response->assertJsonFragment(['type' => 'card']);
    $response->assertJsonFragment(['type' => 'paypal']);
});

it('can return all payment methods when active=false', function () {
    $user = User::factory()->create();

    // create an inactive method
    PaymentMethod::create([
        'name' => 'Crypto',
        'type' => 'crypto',
        'is_active' => false,
        'config' => ['provider' => 'coinbase'],
        'sort_order' => 30,
    ]);

    $response = test()->actingAs($user, 'sanctum')->getJson('/api/v1/wallet/payment-methods?active=0');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
});
