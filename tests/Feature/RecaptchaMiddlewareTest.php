<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\postJson;

it('passes middleware when recaptcha verifies', function () {
    config(['services.recaptcha.secret' => 'secret']);
    Http::fake([
        'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
    ]);

    Route::post('/test/recaptcha', function () { return response()->json(['ok' => true]); })->middleware('recaptcha');

    postJson('/test/recaptcha', ['recaptcha_token' => 'abc'])
        ->assertOk();
});

it('fails middleware when recaptcha missing', function () {
    config(['services.recaptcha.secret' => 'secret']);
    Route::post('/test/recaptcha2', function () { return response()->json(['ok' => true]); })->middleware('recaptcha');
    postJson('/test/recaptcha2')
        ->assertStatus(422);
});
