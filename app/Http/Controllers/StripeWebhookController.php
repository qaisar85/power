<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletTopupOrder;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                /** @var \Stripe\Checkout\Session $session */
                $session = $event->data->object;
                if (($session->payment_status ?? null) === 'paid') {
                    // Wallet top-up flow
                    $userId = (int) ($session->metadata['user_id'] ?? 0);
                    $amountUsd = (float) ($session->metadata['topup_amount_usd'] ?? 0);
                    $amountNative = (float) ($session->metadata['topup_amount_native'] ?? 0);
                    $currencyNative = (string) ($session->metadata['topup_currency_native'] ?? 'USD');

                    if ($userId && $amountUsd > 0) {
                        DB::transaction(function () use ($userId, $amountUsd, $amountNative, $currencyNative, $session) {
                            $order = WalletTopupOrder::lockForUpdate()
                                ->where('provider', 'stripe')
                                ->where('provider_session_id', $session->id)
                                ->first();

                            if (! $order) {
                                $order = WalletTopupOrder::create([
                                    'user_id' => $userId,
                                    'provider' => 'stripe',
                                    'provider_session_id' => $session->id,
                                    'provider_order_id' => $session->payment_intent ?? null,
                                    'status' => 'approved',
                                    'amount_native' => $amountNative,
                                    'currency_native' => $currencyNative,
                                    'amount_usd' => $amountUsd,
                                    'metadata' => [
                                        'payment_status' => $session->payment_status,
                                    ],
                                ]);
                            }

                            if ($order->status === 'completed') {
                                return; // Idempotent guard
                            }

                            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                                ['user_id' => $userId],
                                ['balance' => 0, 'currency' => 'USD']
                            );

                            $wallet->balance = bcadd((string) $wallet->balance, (string) $amountUsd, 2);
                            $wallet->save();

                            WalletTransaction::create([
                                'user_id' => $userId,
                                'amount' => $amountUsd,
                                'amount_usd' => $amountUsd,
                                'type' => 'credit',
                                'description' => 'Wallet top-up (Stripe card)',
                                'meta' => [
                                    'method' => 'card',
                                    'provider' => 'stripe',
                                    'checkout_session_id' => $session->id,
                                    'payment_intent' => $session->payment_intent ?? null,
                                    'original_amount' => $amountNative,
                                    'original_currency' => $currencyNative,
                                ],
                                'balance_after' => $wallet->balance,
                            ]);

                            $order->status = 'completed';
                            $order->provider_order_id = $session->payment_intent ?? $order->provider_order_id;
                            $order->save();
                        });

                        Log::info('Wallet credited via Stripe checkout session', [
                            'user_id' => $userId,
                            'amount_usd' => $amountUsd,
                            'session_id' => $session->id,
                        ]);
                    }

                    // Deposit release for auctions (optional metadata-driven)
                    $action = $session->metadata['action'] ?? null;
                    if ($action === 'auction.final_payment') {
                        $listingId = (int) ($session->metadata['listing_id'] ?? 0);
                        $buyerId = (int) ($session->metadata['buyer_user_id'] ?? 0);
                        if ($listingId && $buyerId) {
                            try {
                                DB::transaction(function () use ($listingId, $buyerId, $session) {
                                    $listing = Listing::find($listingId);
                                    if (! $listing) {
                                        throw new \RuntimeException('Listing not found for deposit release');
                                    }
                                    $sellerId = (int) ($listing->user_id ?? 0);
                                    if (! $sellerId) {
                                        throw new \RuntimeException('Seller not found for listing');
                                    }

                                    // Find latest hold transaction for this buyer & listing
                                    $holdTx = WalletTransaction::lockForUpdate()
                                        ->where('user_id', $buyerId)
                                        ->where('type', 'debit')
                                        ->where('meta->related_service', 'auction')
                                        ->where('meta->listing_id', $listingId)
                                        ->where('meta->hold', true)
                                        ->orderByDesc('id')
                                        ->first();

                                    if (! $holdTx) {
                                        throw new \RuntimeException('No deposit hold found to release');
                                    }

                                    $alreadyReleased = is_array($holdTx->meta ?? null) && !empty(($holdTx->meta)['released_at'] ?? null);
                                    if ($alreadyReleased) {
                                        // Idempotent guard: deposit already released
                                        return;
                                    }
                                    // Credit seller's wallet with USD equivalent of the hold
                                    $sellerWallet = Wallet::lockForUpdate()->firstOrCreate(
                                        ['user_id' => $sellerId],
                                        ['balance' => 0, 'currency' => 'USD']
                                    );

                                    $creditUsd = (float) ($holdTx->amount_usd ?? $holdTx->amount);
                                    $sellerWallet->balance = bcadd((string) $sellerWallet->balance, (string) $creditUsd, 2);
                                    $sellerWallet->save();

                                    WalletTransaction::create([
                                        'user_id' => $sellerId,
                                        'amount' => $creditUsd,
                                        'amount_usd' => $creditUsd,
                                        'type' => 'credit',
                                        'description' => 'Auction deposit transfer (payment captured)',
                                        'meta' => [
                                            'related_service' => 'auction',
                                            'listing_id' => $listingId,
                                            'buyer_user_id' => $buyerId,
                                            'checkout_session_id' => $session->id,
                                        ],
                                        'balance_after' => $sellerWallet->balance,
                                    ]);

                                    // Mark hold transaction as released
                                    $meta = $holdTx->meta ?? [];
                                    $meta['released_at'] = now()->toDateTimeString();
                                    $meta['released_to_user_id'] = $sellerId;
                                    $meta['release_checkout_session_id'] = $session->id;
                                    $meta['release_action'] = 'transfer_to_seller';
                                    $holdTx->meta = $meta;
                                    $holdTx->save();
                                });

                                Log::info('Auction deposit released to seller', [
                                    'listing_id' => $listingId,
                                    'buyer_user_id' => $buyerId,
                                    'checkout_session_id' => $session->id,
                                ]);
                            } catch (\Throwable $e) {
                                Log::error('Auction deposit release failed', [
                                    'listing_id' => $listingId,
                                    'buyer_user_id' => $buyerId,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }
                }
                break;
            default:
                // other events not handled
                break;
        }

        return response()->json(['received' => true]);
    }
}