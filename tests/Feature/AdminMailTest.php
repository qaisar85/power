<?php

use Illuminate\Support\Facades\Mail;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('sends test email from admin endpoint', function () {
    Mail::fake();
    $admin = \App\Models\User::factory()->create(['user_type' => 'admin']);
    actingAs($admin instanceof \Illuminate\Contracts\Auth\Authenticatable ? $admin : $admin->first());
    post('/admin/mail/test', ['to' => 'recipient@example.com'])
        ->assertRedirect();
    Mail::assertSent(function () { return true; });
});
