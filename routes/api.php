<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SSOController;
use App\Http\Controllers\Api\SectorController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// SSO Routes for module integration
Route::prefix('auth')->group(function() {
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/refresh', [\App\Http\Controllers\Api\AuthController::class, 'refresh'])->middleware('auth:sanctum');
    Route::post('/verify-token', [\App\Http\Controllers\Api\AuthController::class, 'verifyToken']);
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::post('/sso/token', [SSOController::class, 'generateToken'])->middleware('auth:sanctum');
    Route::post('/sso/validate', [SSOController::class, 'validateToken']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', function (\Illuminate\Http\Request $request) {
        $user = $request->user()->load('companies');
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'companies' => $user->companies,
            'roles' => $user->getRoleNames(),
        ]);
    });

    Route::get('/modules', function () {
        $modules = \App\Models\Module::active()->orderBy('sort_order')->get([ 'id','name','slug','path','description','icon','integration_type' ]);
        return response()->json($modules);
    });

    Route::post('/notify', function (\Illuminate\Http\Request $request) {
        // Placeholder notification endpoint; extend to queue/email later
        return response()->json(['ok' => true]);
    });
});

Route::get('/sectors', [SectorController::class, 'index']);
Route::get('/sectors/{code}', [SectorController::class, 'show']);

// Stripe webhook (public, no CSRF)
Route::post('/webhooks/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// PayPal webhook
Route::post('/webhooks/paypal', [\App\Http\Controllers\PayPalWebhookController::class, 'handle'])->name('paypal.webhook');

// Include API V1 routes for mobile applications
require __DIR__.'/api_v1.php';
