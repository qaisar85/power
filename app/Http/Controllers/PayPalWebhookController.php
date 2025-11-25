<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WalletTopupOrder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class PayPalWebhookController extends Controller
{
    protected function apiBase(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function getAccessToken(): ?string
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        if (! $clientId || ! $secret) {
            Log::error('PayPal credentials missing');
            return null;
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->post($this->apiBase() . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if (! $response->successful()) {
            Log::error('PayPal token request failed (webhook verify)', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $response->json('access_token');
    }

    public function handle(Request $request)
    {
        // Verify webhook signature if webhook_id is configured
        $webhookId = config('services.paypal.webhook_id');
        $rawBody = $request->getContent();
        $headers = [
            'transmission_id' => $request->header('paypal-transmission-id'),
            'transmission_time' => $request->header('paypal-transmission-time'),
            'cert_url' => $request->header('paypal-cert-url'),
            'auth_algo' => $request->header('paypal-auth-algo'),
            'transmission_sig' => $request->header('paypal-transmission-sig'),
        ];

        $verified = false;
        if ($webhookId && $headers['transmission_id'] && $headers['transmission_sig']) {
            $token = $this->getAccessToken();
            if ($token) {
                $verifyRes = Http::withToken($token)->post($this->apiBase() . '/v1/notifications/verify-webhook-signature', [
                    'auth_algo' => $headers['auth_algo'],
                    'cert_url' => $headers['cert_url'],
                    'transmission_id' => $headers['transmission_id'],
                    'transmission_sig' => $headers['transmission_sig'],
                    'transmission_time' => $headers['transmission_time'],
                    'webhook_id' => $webhookId,
                    'webhook_event' => json_decode($rawBody, true),
                ]);
                $verified = $verifyRes->successful() && $verifyRes->json('verification_status') === 'SUCCESS';
            }
        }

        $eventType = $request->json('event_type');
        $resource = $request->json('resource');

        Log::info('PayPal webhook received', [
            'verified' => $verified,
            'event_type' => $eventType,
            'resource' => $resource,
        ]);

        if ($verified && $eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $captureId = data_get($resource, 'id');
            $amountUsd = (float) data_get($resource, 'amount.value', 0);
            $orderId = data_get($resource, 'supplementary_data.related_ids.order_id')
                ?? collect(data_get($resource, 'links', []))->firstWhere('rel', 'up')['href'] ?? null;
            if (is_string($orderId) && str_starts_with($orderId, 'https://')) {
                // Extract order id from URL if provided as link
                $parts = explode('/', rtrim($orderId, '/'));
                $orderId = end($parts);
            }

            if ($orderId && $amountUsd > 0) {
                DB::transaction(function () use ($orderId, $amountUsd, $captureId) {
                    $order = WalletTopupOrder::lockForUpdate()
                        ->where('provider', 'paypal')
                        ->where('provider_order_id', $orderId)
                        ->first();

                    if (! $order || $order->status === 'completed') {
                        return; // No-op or already credited
                    }

                    $wallet = Wallet::lockForUpdate()->firstOrCreate(
                        ['user_id' => $order->user_id],
                        ['balance' => 0, 'currency' => 'USD']
                    );

                    $wallet->balance = bcadd((string) $wallet->balance, (string) $amountUsd, 2);
                    $wallet->save();

                    WalletTransaction::create([
                        'user_id' => $order->user_id,
                        'amount' => $amountUsd,
                        'amount_usd' => $amountUsd,
                        'type' => 'credit',
                        'description' => 'Wallet top-up (PayPal webhook)',
                        'meta' => [
                            'method' => 'card',
                            'provider' => 'paypal',
                            'order_id' => $orderId,
                            'capture_id' => $captureId,
                            'original_amount' => $order->amount_native,
                            'original_currency' => $order->currency_native,
                        ],
                        'balance_after' => $wallet->balance,
                    ]);

                    $order->status = 'completed';
                    $order->capture_id = $captureId;
                    $order->save();
                });
            }
        }

        return response()->json(['ok' => true]);
    }
}