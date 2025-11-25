<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CurrencyRate;
use App\Models\WalletTopupOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeCheckoutSession;

class StripePaymentsController extends Controller
{
    public function createTopupCheckoutSession(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $amountNative = (float) $data['amount'];
        $currency = strtoupper($data['currency'] ?? 'USD');

        // Convert to USD for Stripe charge
        $amountUsd = CurrencyRate::toUsd($amountNative, $currency);
        $amountUsdCents = max(1, (int) round(((float) $amountUsd) * 100));

        Stripe::setApiKey(config('services.stripe.secret'));

        $successUrl = route('wallet.topup.card.success');
        $cancelUrl = route('wallet.topup.card.cancel');

        $session = StripeCheckoutSession::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Wallet Top-up',
                        'description' => 'Account balance top-up',
                    ],
                    'unit_amount' => $amountUsdCents,
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'user_id' => (string) $user->id,
                'topup_amount_native' => (string) $amountNative,
                'topup_currency_native' => $currency,
                'topup_amount_usd' => (string) $amountUsd,
            ],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
        ]);

        Log::info('Stripe Checkout session created', [
            'user_id' => $user->id,
            'session_id' => $session->id,
            'amount_usd' => $amountUsd,
        ]);

        try {
            WalletTopupOrder::create([
                'user_id' => $user->id,
                'provider' => 'stripe',
                'provider_session_id' => $session->id,
                'status' => 'created',
                'amount_native' => $amountNative,
                'currency_native' => $currency,
                'amount_usd' => (float) $amountUsd,
                'metadata' => [
                    'payment_intent' => $session->payment_intent ?? null,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist WalletTopupOrder (Stripe)', ['error' => $e->getMessage()]);
        }

        return Redirect::away($session->url);
    }

    public function success(Request $request)
    {
        // We rely on the webhook to credit the wallet.
        // This endpoint just informs the user and returns them to Packages.
        return redirect()->route('packages.index')
            ->with('success', 'Payment received. Wallet will be credited after confirmation.');
    }

    public function cancel(Request $request)
    {
        return redirect()->route('packages.index')
            ->with('error', 'Card payment canceled. No charges applied.');
    }

    public function createCheckout(Request $request)
    {
        return $this->createTopupCheckoutSession($request);
    }
}