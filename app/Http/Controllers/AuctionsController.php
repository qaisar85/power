<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\AuctionBid;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Events\AuctionBidPlaced;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeCheckoutSession;

class AuctionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query()
            ->where('publish_in_auction', true)
            ->whereIn('status', ['published', 'under_review', 'draft', 'ended', 'sold'])
            ->orderByDesc('created_at');

        $location = trim((string) $request->input('location', ''));
        $category = trim((string) $request->input('category', ''));
        $search = trim((string) $request->input('q', ''));

        if ($location !== '') {
            $query->where('location', 'like', "%{$location}%");
        }
        if ($category !== '') {
            $query->where('category', 'like', "%{$category}%");
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $date = trim((string) $request->input('date', ''));
        $type = trim((string) $request->input('type', ''));

        $lots = $query->limit(100)->get()
            ->map(function ($l) {
                $af = $l->auction_fields ?? [];
                $endAt = $af['end_at'] ?? now()->addDays(3)->toDateTimeString();
                $startPrice = $af['start_price'] ?? ($l->price ?? 0);
                $currentBid = AuctionBid::where('listing_id', $l->id)->orderByDesc('amount')->value('amount');
                $buyNow = $af['buy_now_price'] ?? null;
                $auctionType = $buyNow ? 'Buy It Now' : (now()->lessThan($endAt) ? 'Pre-bid' : 'Finished');
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'description' => str($l->description)->limit(120)->toString(),
                    'currency' => $l->currency ?? 'USD',
                    'currentBid' => $currentBid ?? $startPrice,
                    'buyNowPrice' => $buyNow,
                    'endAt' => $endAt,
                    'photos' => $l->photos ?? [],
                    'status' => $l->status,
                    'location' => $l->location,
                    'category' => $l->category,
                    'auctionType' => $auctionType,
                ];
            })
            ->filter(function ($lot) use ($date, $type) {
                $ok = true;
                if ($date !== '') {
                    $ok = $ok && (str($lot['endAt'])->startsWith($date) || str($lot['endAt'])->contains($date));
                }
                if ($type !== '') {
                    $ok = $ok && (strcasecmp($lot['auctionType'], $type) === 0);
                }
                return $ok;
            })
            ->values();

        return Inertia::render('Auctions/Index', [
            'lots' => $lots,
            'filters' => [
                'location' => $location,
                'category' => $category,
                'q' => $search,
                'date' => $date,
                'type' => $type,
            ],
        ]);
    }

    public function show(Listing $listing)
    {
        abort_unless($listing->publish_in_auction, 404);
        $this->finalizeAuctionIfEnded($listing);

        $af = $listing->auction_fields ?? [];
        $endAt = $af['end_at'] ?? now()->addDays(3)->toDateTimeString();
        $startPrice = $af['start_price'] ?? ($listing->price ?? 0);
        $depositPercent = $af['deposit_percent'] ?? 0.1;
        $buyNowPrice = $af['buy_now_price'] ?? null;
        $minStep = $af['min_step'] ?? 1;
        $timeLimitMinutes = $af['time_limit_minutes'] ?? null;
        $inspectionUntil = $af['inspection_until'] ?? null;
        $documents = $listing->documents ?? [];
        $currentBid = AuctionBid::where('listing_id', $listing->id)->orderByDesc('amount')->value('amount') ?? $startPrice;
        $viewer = request()->user();
        $viewerCanBid = ($viewer && ($viewer->user_package ?? 'basic') !== 'basic');

        $bids = AuctionBid::where('listing_id', $listing->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($b) {
                return [
                    'amount' => $b->amount,
                    'user_id' => $b->user_id,
                    'created_at' => $b->created_at->toDateTimeString(),
                ];
            });

        return Inertia::render('Auctions/Show', [
            'lot' => [
                'id' => $listing->id,
                'title' => $listing->title,
                'description' => $listing->description,
                'currency' => $listing->currency ?? 'USD',
                'currentBid' => $currentBid,
                'buyNowPrice' => $buyNowPrice,
                'endAt' => $endAt,
                'depositPercent' => $depositPercent,
                'photos' => $listing->photos ?? [],
                'documents' => $documents,
                'inspectionUntil' => $inspectionUntil,
                'viewerCanBid' => $viewerCanBid,
                'viewerUserId' => $viewer ? $viewer->id : null,
                'minStep' => $minStep,
                'timeLimitMinutes' => $timeLimitMinutes,
                'bids' => $bids,
                'winnerUserId' => $af['winner_user_id'] ?? null,
                'finalizedAt' => $af['finalized_at'] ?? null,
                'status' => $listing->status,
            ],
        ]);
    }

    public function sample()
    {
        $user = request()->user();
        abort_unless($user, 401);

        $lot = Listing::where('publish_in_auction', true)->first();
        if (!$lot) {
            $lot = Listing::create([
                'user_id' => $user->id,
                'role' => 'company',
                'type' => 'product',
                'title' => 'Auction Lot: Industrial Compressor Z-500',
                'description' => 'Heavy-duty compressor suitable for large industrial operations. Fully serviced.',
                'price' => 25000,
                'currency' => 'USD',
                'status' => 'published',
                'location' => 'Dubai, UAE',
                'deal_type' => 'auction',
                'category' => 'Compressors',
                'photos' => [],
                'publish_in_auction' => true,
                'auction_fields' => [
                    'start_price' => 20000,
                    'buy_now_price' => 40000,
                    'deposit_percent' => 0.1,
                    'min_step' => 500,
                    'time_limit_minutes' => 2,
                    'inspection_until' => now()->addDays(2)->toDateString(),
                    'end_at' => now()->addDays(5)->toDateTimeString(),
                ],
            ]);
        }

        return redirect()->route('auctions.show', $lot);
    }

    public function bid(Request $request, Listing $listing)
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($listing->publish_in_auction, 404);

        $af = $listing->auction_fields ?? [];
        $endAt = $af['end_at'] ?? null;
        if ($endAt && now()->greaterThan($endAt)) {
            return back()->withErrors(['amount' => 'Auction has ended']);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $depositPercent = (float) ($af['deposit_percent'] ?? 0.1);

        try {
            DB::transaction(function () use ($listing, $user, $data, $depositPercent, $af) {
                // Re-check current top bid and min step inside transaction
                $currentBid = AuctionBid::where('listing_id', $listing->id)->orderByDesc('amount')->value('amount');
                $minStep = $af['min_step'] ?? 1;
                $base = max($currentBid ?? ($af['start_price'] ?? ($listing->price ?? 0)), 0);
                $minAllowed = $base + $minStep;
                if ($data['amount'] < $minAllowed) {
                    throw new \RuntimeException('Bid must be at least ' . $minAllowed);
                }

                // Optional per-user time limit
                $timeLimit = $af['time_limit_minutes'] ?? null;
                if ($timeLimit) {
                    $lastBidAt = AuctionBid::where('listing_id', $listing->id)->orderByDesc('created_at')->value('created_at');
                    if ($lastBidAt && now()->lt($lastBidAt->copy()->addMinutes($timeLimit))) {
                        throw new \RuntimeException('Please wait before placing another bid.');
                    }
                }

                // Ensure wallet exists and lock it for update
                $wallet = \App\Models\Wallet::lockForUpdate()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'currency' => 'USD']
                );

                // Refund previous deposit hold for this user on this listing (if any)
                $previousBid = AuctionBid::where('listing_id', $listing->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'accepted')
                    ->orderByDesc('amount')
                    ->first();

                if ($previousBid) {
                    $refundNative = round(((float) $previousBid->amount) * $depositPercent, 2);
                    if ($refundNative > 0) {
                        $refundConverted = $wallet->currency === ($listing->currency ?? 'USD')
                            ? $refundNative
                            : \App\Models\CurrencyRate::convert($refundNative, ($listing->currency ?? 'USD'), $wallet->currency);
                        if ($refundConverted === null) {
                            throw new \RuntimeException('Missing conversion rate for refund');
                        }
                        $amountUsd = \App\Models\CurrencyRate::toUsd($refundNative, ($listing->currency ?? 'USD'));
                        $wallet->balance = bcadd((string) $wallet->balance, (string) $refundConverted, 2);
                        $wallet->save();

                        \App\Models\WalletTransaction::create([
                            'user_id' => $user->id,
                            'amount' => (float) $refundConverted,
                            'amount_usd' => (float) $amountUsd,
                            'type' => 'credit',
                            'description' => 'Auction bid deposit refund',
                            'meta' => [
                                'related_service' => 'auction',
                                'listing_id' => $listing->id,
                                'previous_bid_id' => $previousBid->id,
                                'refund_native' => [
                                    'amount' => $refundNative,
                                    'currency' => ($listing->currency ?? 'USD'),
                                ],
                                'wallet_currency' => $wallet->currency,
                            ],
                            'balance_after' => $wallet->balance,
                        ]);

                        $previousBid->status = 'refunded';
                        $previousBid->save();
                    }
                }

                // Required deposit for new bid
                $depositNative = round(((float) $data['amount']) * $depositPercent, 2);
                $depositConverted = $wallet->currency === ($listing->currency ?? 'USD')
                    ? $depositNative
                    : \App\Models\CurrencyRate::convert($depositNative, ($listing->currency ?? 'USD'), $wallet->currency);

                if ($depositConverted === null) {
                    throw new \RuntimeException('Missing conversion rate for deposit');
                }

                if ((float) $wallet->balance < (float) $depositConverted) {
                    throw new \RuntimeException('Insufficient wallet balance. Required deposit: ' . number_format($depositConverted, 2) . ' ' . $wallet->currency);
                }

                $amountUsd = \App\Models\CurrencyRate::toUsd($depositNative, ($listing->currency ?? 'USD'));
                $wallet->balance = bcsub((string) $wallet->balance, (string) $depositConverted, 2);
                $wallet->save();

                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => (float) $depositConverted,
                    'amount_usd' => (float) $amountUsd,
                    'type' => 'debit',
                    'description' => 'Auction bid deposit hold',
                    'meta' => [
                        'related_service' => 'auction',
                        'listing_id' => $listing->id,
                        'auction_bid_id' => null,
                        'deposit_percent' => $depositPercent,
                        'hold' => true,
                        'deposit_native' => [
                            'amount' => $depositNative,
                            'currency' => ($listing->currency ?? 'USD'),
                        ],
                        'wallet_currency' => $wallet->currency,
                    ],
                    'balance_after' => $wallet->balance,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        // Broadcast bid placement for realtime updates
        try {
            broadcast(new AuctionBidPlaced($listing->id, $user->id, (float) $data['amount']));
        } catch (\Throwable $e) {
            Log::warning('Auction bid broadcast failed', ['listing_id' => $listing->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('auctions.show', $listing)->with('success', 'Bid placed successfully');
    }

    public function buyNow(\Illuminate\Http\Request $request, \App\Models\Listing $listing)
    {
        $user = $request->user();
        $af = $listing->auction_fields ?? [];
        $buyNowPrice = (float) ($af['buy_now_price'] ?? 0);

        if ($listing->status === 'sold' || $buyNowPrice <= 0) {
            return back()->withErrors(['amount' => 'Buy Now is not available for this listing.']);
        }

        $depositPercent = (float) ($af['deposit_percent'] ?? 0.1);

        try {
            \DB::transaction(function () use ($listing, $user, $buyNowPrice, $depositPercent, $af) {
                $wallet = \App\Models\Wallet::lockForUpdate()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'currency' => 'USD']
                );

                $depositNative = round($buyNowPrice * $depositPercent, 2);
                $depositConverted = $wallet->currency === ($listing->currency ?? 'USD')
                    ? $depositNative
                    : \App\Models\CurrencyRate::convert($depositNative, ($listing->currency ?? 'USD'), $wallet->currency);
                if ($depositConverted === null) {
                    throw new \RuntimeException('Missing conversion rate for buy-now deposit');
                }

                if ((float) $wallet->balance < (float) $depositConverted) {
                    throw new \RuntimeException('Insufficient wallet balance. Required deposit: ' . number_format($depositConverted, 2) . ' ' . $wallet->currency);
                }

                $amountUsd = \App\Models\CurrencyRate::toUsd($depositNative, ($listing->currency ?? 'USD'));

                $wallet->balance = bcsub((string) $wallet->balance, (string) $depositConverted, 2);
                $wallet->save();

                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => (float) $depositConverted,
                    'amount_usd' => (float) $amountUsd,
                    'type' => 'debit',
                    'description' => 'Auction buy-now deposit hold',
                    'meta' => [
                        'related_service' => 'auction',
                        'listing_id' => $listing->id,
                        'buy_now_price_native' => [
                            'amount' => (float) $buyNowPrice,
                            'currency' => ($listing->currency ?? 'USD'),
                        ],
                        'deposit_percent' => $depositPercent,
                        'hold' => true,
                        'deposit_native' => [
                            'amount' => $depositNative,
                            'currency' => ($listing->currency ?? 'USD'),
                        ],
                        'wallet_currency' => $wallet->currency,
                    ],
                    'balance_after' => $wallet->balance,
                ]);

                $listing->status = 'sold';
                $listing->auction_fields = array_merge($af, [
                    'winner_user_id' => $user->id,
                    'finalized_at' => now()->toDateTimeString(),
                ]);
                $listing->save();
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        \Log::info('Listing purchased via Buy Now', [
            'listing_id' => $listing->id,
            'user_id' => $user->id,
            'deposit_percent' => $depositPercent,
            'currency' => ($listing->currency ?? 'USD'),
        ]);

        return redirect()->route('auctions.show', $listing->id)->with('success', 'You purchased this lot.');
    }

    private function finalizeAuctionIfEnded(Listing $listing): void
    {
        $af = $listing->auction_fields ?? [];
        $endAt = $af['end_at'] ?? null;
        if (!$endAt) {
            return;
        }
        if (in_array($listing->status, ['sold', 'ended'])) {
            return;
        }
        if (now()->greaterThan($endAt)) {
            $topBid = AuctionBid::where('listing_id', $listing->id)->orderByDesc('amount')->first();
            if ($topBid) {
                $listing->status = 'sold';
                $af['winner_user_id'] = $topBid->user_id;
                $af['finalized_at'] = now()->toDateTimeString();
                $listing->auction_fields = $af;
                $listing->save();

                // Refund deposits for losing bidders (idempotent via bid status update)
                $depositPercent = (float) ($af['deposit_percent'] ?? 0.1);
                DB::transaction(function () use ($listing, $topBid, $depositPercent) {
                    $losingBids = AuctionBid::where('listing_id', $listing->id)
                        ->where('status', 'accepted')
                        ->where('user_id', '!=', $topBid->user_id)
                        ->get();

                    foreach ($losingBids as $bid) {
                        $wallet = Wallet::lockForUpdate()->firstOrCreate(
                            ['user_id' => $bid->user_id],
                            ['balance' => 0, 'currency' => $listing->currency ?? 'USD']
                        );
                        $refund = round(((float) $bid->amount) * $depositPercent, 2);
                        if ($refund > 0) {
                            $wallet->balance = bcadd((string) $wallet->balance, (string) $refund, 2);
                            $wallet->save();

                            WalletTransaction::create([
                                'user_id' => $bid->user_id,
                                'amount' => $refund,
                                'amount_usd' => \App\Models\CurrencyRate::toUsd($refund, $wallet->currency),
                                'type' => 'credit',
                                'description' => 'Auction deposit refund (finalization)',
                                'meta' => [
                                    'related_service' => 'auction',
                                    'listing_id' => $listing->id,
                                    'lost_bid_id' => $bid->id,
                                ],
                                'balance_after' => $wallet->balance,
                            ]);

                            $bid->status = 'refunded';
                            $bid->save();
                        }
                    }
                });

                Log::info('Auction finalized with winner', ['listing_id' => $listing->id, 'winner' => $topBid->user_id]);
            } else {
                $listing->status = 'ended';
                $af['finalized_at'] = now()->toDateTimeString();
                $listing->auction_fields = $af;
                $listing->save();
                Log::info('Auction finalized without bids', ['listing_id' => $listing->id]);
            }
        }
    }

    public function finalPaymentCheckout(Request $request, Listing $listing)
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($listing->publish_in_auction, 404);

        $af = $listing->auction_fields ?? [];
        $winnerId = (int) ($af['winner_user_id'] ?? 0);
        if (!$winnerId || $winnerId !== (int)$user->id) {
            return redirect()->route('auctions.show', $listing)->with('error', 'Only the winner can complete the final payment.');
        }

        $currency = $listing->currency ?? 'USD';
        $depositPercent = (float) ($af['deposit_percent'] ?? 0.1);

        // Determine sale price (buy-now or top bid)
        $salePriceNative = null;
        if (!empty($af['buy_now_price'])) {
            $salePriceNative = (float) $af['buy_now_price'];
        } else {
            $topBid = AuctionBid::where('listing_id', $listing->id)->orderByDesc('amount')->first();
            if (!$topBid || (int)$topBid->user_id !== $winnerId) {
                return redirect()->route('auctions.show', $listing)->with('error', 'Winning bid not found.');
            }
            $salePriceNative = (float) $topBid->amount;
        }

        $depositNative = round($salePriceNative * $depositPercent, 2);
        $finalAmountNative = max(0.01, round($salePriceNative - $depositNative, 2));

        // Convert to USD for Stripe charge
        $amountUsd = \App\Models\CurrencyRate::toUsd($finalAmountNative, $currency);
        $amountUsdCents = max(1, (int) round(((float) $amountUsd) * 100));

        Stripe::setApiKey(config('services.stripe.secret'));

        $successUrl = route('auctions.final_payment.success', $listing) . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('auctions.final_payment.cancel', $listing);

        $session = StripeCheckoutSession::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Auction Final Payment',
                        'description' => 'Listing #' . $listing->id . ' — ' . ($listing->title ?? 'Lot'),
                    ],
                    'unit_amount' => $amountUsdCents,
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'action' => 'auction.final_payment',
                'listing_id' => (string) $listing->id,
                'buyer_user_id' => (string) $user->id,
                'final_amount_native' => (string) $finalAmountNative,
                'final_currency_native' => $currency,
                'final_amount_usd' => (string) $amountUsd,
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        Log::info('Stripe Checkout session (final payment) created', [
            'user_id' => $user->id,
            'listing_id' => $listing->id,
            'session_id' => $session->id,
            'amount_usd' => $amountUsd,
        ]);

        return \Illuminate\Support\Facades\Redirect::away($session->url);
    }

    public function finalPaymentSuccess(Request $request, Listing $listing)
    {
        return redirect()->route('auctions.show', $listing)
            ->with('success', 'Payment received. Deposit will be released to the seller after confirmation.');
    }

    public function finalPaymentCancel(Request $request, Listing $listing)
    {
        return redirect()->route('auctions.show', $listing)
            ->with('error', 'Final payment canceled. No charges applied.');
    }
}