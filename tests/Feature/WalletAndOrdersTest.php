<?php

use App\Models\User;
use App\Models\FreelanceService;
use App\Models\Wallet;
use App\Models\Setting;
use App\Models\PlatformFee;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;

it('allows wallet transfer between users', function () {
    $from = User::factory()->create();
    $to = User::factory()->create();

    Wallet::firstOrCreate(['user_id' => $from->id], ['balance' => 100, 'currency' => 'USD']);
    Wallet::firstOrCreate(['user_id' => $to->id], ['balance' => 0, 'currency' => 'USD']);

    actingAs($from instanceof \Illuminate\Contracts\Auth\Authenticatable ? $from : $from->first(), 'sanctum');
    postJson('/api/v1/wallet/transfer', [
        'to_user_id' => $to->id,
        'amount' => 25,
        ])->assertOk();
});

it('creates a paid freelance order from wallet', function () {
    $buyer = User::factory()->create();
    $seller = User::factory()->create();

    Wallet::firstOrCreate(['user_id' => $buyer->id], ['balance' => 100, 'currency' => 'USD']);
    Wallet::firstOrCreate(['user_id' => $seller->id], ['balance' => 0, 'currency' => 'USD']);

    Setting::set('platform_fee_percentage', 10);

    $service = FreelanceService::create([
        'user_id' => $seller->id,
        'title' => 'Logo Design',
        'slug' => 'logo-'.uniqid(),
        'description' => 'Pro logo design',
        'price_type' => 'fixed',
        'price_value' => 50,
        'currency' => 'USD',
        'status' => 'approved',
    ]);

    actingAs($buyer instanceof \Illuminate\Contracts\Auth\Authenticatable ? $buyer : $buyer->first(), 'sanctum');
    postJson('/api/v1/freelance/orders/service/'.$service->id, [])
        ->assertCreated();
    // Seller should receive 90% (platform fee 10%)
    $sellerWallet = Wallet::where('user_id', $seller->id)->first();
    expect((float) $sellerWallet->balance)->toBe(45.0);

    $order = \App\Models\FreelanceOrder::latest()->first();
    $fee = PlatformFee::where('order_id', $order->id)->first();
    expect($fee)->not()->toBeNull();
    expect((float) $fee->amount)->toBe(5.0);
});

it('credits platform wallet on fee collection when configured', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);
    $platform = User::factory()->create();
    Setting::set('platform_wallet_user_id', $platform->id);

    $fee = PlatformFee::create([
        'order_id' => 1,
        'amount' => 12.34,
        'currency' => 'USD',
        'status' => 'pending',
    ]);

    actingAs($admin instanceof \Illuminate\Contracts\Auth\Authenticatable ? $admin : $admin->first(), 'web');
    post('/admin/finance/platform-fees/'.$fee->id.'/collect')
        ->assertRedirect();

    $wallet = Wallet::where('user_id', $platform->id)->first();
    expect($wallet)->not()->toBeNull();
    expect((float) $wallet->balance)->toBe(12.34);
});

it('computes rating average for a service', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $service = FreelanceService::create([
        'user_id' => $seller->id,
        'title' => 'Design',
        'slug' => 'design-'.uniqid(),
        'price_type' => 'fixed',
        'price_value' => 10,
        'currency' => 'USD',
        'status' => 'approved',
    ]);
    \App\Models\Review::create(['service_id' => $service->id, 'user_id' => $user->id, 'stars' => 4, 'status' => 'visible']);
    \App\Models\Review::create(['service_id' => $service->id, 'user_id' => $user->id, 'stars' => 5, 'status' => 'visible']);
    actingAs($user instanceof \Illuminate\Contracts\Auth\Authenticatable ? $user : $user->first(), 'sanctum');
    getJson('/api/v1/freelance/services/'.$service->id.'/rating')
        ->assertOk();
});
    
