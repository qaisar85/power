<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CurrencyRate;
use App\Models\WalletTopupOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class PayPalPaymentsController extends Controller
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
            Log::error('PayPal token request failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        return $response->json('access_token');
    }

    public function createTopupOrder(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $amountNative = (float) $data['amount'];
        $currency = strtoupper($data['currency'] ?? 'USD');

        $amountUsd = CurrencyRate::toUsd($amountNative, $currency);
        $amountUsdStr = number_format((float) $amountUsd, 2, '.', '');

        $token = $this->getAccessToken();
        if (! $token) {
            return redirect()->route('packages.index')->with('error', 'Unable to initialize PayPal.');
        }

        $returnUrl = route('wallet.topup.paypal.return');
        $cancelUrl = route('wallet.topup.paypal.cancel');

        $orderRes = Http::withToken($token)->post($this->apiBase() . '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => $amountUsdStr,
                ],
                'description' => 'Wallet Top-up',
                'custom_id' => 'topup_user_' . $user->id,
            ]],
            'application_context' => [
                'brand_name' => config('app.name'),
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
            ],
        ]);

        if (! $orderRes->successful()) {
            Log::error('PayPal order creation failed', ['status' => $orderRes->status(), 'body' => $orderRes->body()]);
            return redirect()->route('packages.index')->with('error', 'Unable to start PayPal payment.');
        }

        $order = $orderRes->json();
        $approveLink = collect($order['links'] ?? [])->firstWhere('rel', 'approve')['href'] ?? null;
        if (! $approveLink) {
            Log::error('PayPal approve link missing', ['order' => $order]);
            return redirect()->route('packages.index')->with('error', 'Unable to redirect to PayPal.');
        }

        // Persist order mapping for webhook/return reconciliation
        try {
            WalletTopupOrder::create([
                'user_id' => $user->id,
                'provider' => 'paypal',
                'provider_order_id' => $order['id'] ?? null,
                'status' => 'created',
                'amount_native' => $amountNative,
                'currency_native' => $currency,
                'amount_usd' => (float) $amountUsdStr,
                'metadata' => [
                    'custom_id' => 'topup_user_' . $user->id,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist WalletTopupOrder', ['error' => $e->getMessage()]);
        }

        return Redirect::away($approveLink);
    }

    public function captureReturn(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $orderId = $request->query('token'); // PayPal returns ?token=<orderId>
        if (! $orderId) {
            return redirect()->route('packages.index')->with('error', 'Missing PayPal order token.');
        }

        $token = $this->getAccessToken();
        if (! $token) {
            return redirect()->route('packages.index')->with('error', 'Unable to capture PayPal payment.');
        }

        $captureRes = Http::withToken($token)->post($this->apiBase() . "/v2/checkout/orders/{$orderId}/capture");
        if (! $captureRes->successful()) {
            Log::error('PayPal capture failed', ['status' => $captureRes->status(), 'body' => $captureRes->body()]);
            return redirect()->route('packages.index')->with('error', 'PayPal payment capture failed.');
        }

        $capture = $captureRes->json();
        $status = $capture['status'] ?? 'UNKNOWN';
        if ($status !== 'COMPLETED') {
            return redirect()->route('packages.index')->with('error', 'PayPal payment not completed.');
        }

        $amountUsdStr = $capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? '0.00';
        $amountUsd = (float) $amountUsdStr;
        $captureId = $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

        DB::transaction(function () use ($user, $amountUsd, $captureId, $orderId) {
            $order = WalletTopupOrder::lockForUpdate()
                ->where('provider', 'paypal')
                ->where('provider_order_id', $orderId)
                ->first();

            if (! $order) {
                $order = WalletTopupOrder::create([
                    'user_id' => $user->id,
                    'provider' => 'paypal',
                    'provider_order_id' => $orderId,
                    'status' => 'approved',
                    'amount_native' => 0,
                    'currency_native' => 'USD',
                    'amount_usd' => $amountUsd,
                ]);
            }

            if ($order->status === 'completed') {
                return; // Idempotent guard
            }

            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'USD']
            );

            $wallet->balance = bcadd((string) $wallet->balance, (string) $amountUsd, 2);
            $wallet->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $amountUsd,
                'amount_usd' => $amountUsd,
                'type' => 'credit',
                'description' => 'Wallet top-up (PayPal)',
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

        return redirect()->route('packages.index')->with('success', 'PayPal payment completed. Wallet credited.');
    }

    public function cancel(Request $request)
    {
        return redirect()->route('packages.index')->with('error', 'PayPal payment canceled.');
    }
}