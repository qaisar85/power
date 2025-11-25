<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CurrencyRate;
use App\Models\Promotion;
use App\Models\PromotionRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PackagesController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        // Ensure wallet exists
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'USD']
        );

        // Fetch recent transactions
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        // Load available packages from DB, fallback to defaults if none
        $packages = \App\Models\Package::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'slug' => $p->slug,
                    'name' => $p->name,
                    'price' => $p->price,
                    'currency' => $p->currency,
                    'duration_days' => $p->duration_days,
                    'features' => $p->features ?? [],
                ];
            });

        if ($packages->isEmpty()) {
            $packages = collect([
                [
                    'slug' => 'free-demo',
                    'name' => 'Free Demo',
                    'price' => 0,
                    'currency' => 'USD',
                    'duration_days' => 30,
                    'features' => [
                        'Post 1 free listing per week',
                        'Contacts masked; request flow enabled',
                        'Manual moderation required',
                    ],
                ],
                [
                    'slug' => 'standard',
                    'name' => 'Standard',
                    'price' => 49,
                    'currency' => 'USD',
                    'duration_days' => 30,
                    'features' => [
                        'Publish up to 20 listings/month',
                        'Contacts visible to viewers',
                        'Priority moderation',
                    ],
                ],
                [
                    'slug' => 'premium',
                    'name' => 'Premium',
                    'price' => 149,
                    'currency' => 'USD',
                    'duration_days' => 30,
                    'features' => [
                        'Unlimited listings',
                        'Contacts visible; homepage feature',
                        'Fast-track moderation',
                    ],
                ],
            ]);
        }

        return Inertia::render('Packages/Index', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'packages' => $packages,
            'userPackage' => $user->user_package ?? 'basic',
            'activeUserPackage' => \App\Models\UserPackage::with('package')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->orderByDesc('activated_at')
                ->first(),
        ]);
    }

    public function topup(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = (float) $validated['amount'];

        DB::transaction(function () use ($user, $amount) {
            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'USD']
            );

            $wallet->balance = bcadd((string) $wallet->balance, (string) $amount, 2);
            $wallet->save();

            $amountUsd = CurrencyRate::toUsd($amount, $wallet->currency);

            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'amount_usd' => $amountUsd,
                'type' => 'credit',
                'description' => 'Balance top-up (stub)',
                'meta' => ['method' => 'card_stub'],
                'balance_after' => $wallet->balance,
            ]);
        });

        return redirect()->back()->with('success', 'Balance topped up successfully.');
    }

    public function topupManual(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'string', 'in:bank_transfer,crypto'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        \App\Models\PaymentRequest::create([
            'user_id' => $user->id,
            'amount' => (float) $data['amount'],
            'currency' => 'USD', // extend to user-selected currency later
            'method' => $data['method'],
            'status' => 'pending',
            'reference' => $data['reference'] ?? null,
            'meta' => [
                'created_via' => 'dashboard',
            ],
        ]);

        return redirect()->back()->with('success', 'Payment request submitted. We will verify and credit your wallet.');
    }

    public function subscribe(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $data = $request->validate([
            'package_id' => ['required', 'integer', 'exists:packages,id'],
            'promo_code' => ['nullable', 'string'],
        ]);

        $package = \App\Models\Package::findOrFail($data['package_id']);

        $currency = $package->currency;
        $promo = null;
        if (!empty($data['promo_code'])) {
            $promo = Promotion::active()->where('code', $data['promo_code'])->first();
            if (!$promo || !$promo->isCurrentlyValid() || !$promo->appliesToPackage($package->id)) {
                return redirect()->back()->with('error', 'Invalid or expired promo code.');
            }
        }

        try {
            DB::transaction(function () use ($user, $package, $currency, $promo) {
                $wallet = Wallet::lockForUpdate()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0, 'currency' => $currency]
                );

                $priceNative = (float) $package->price; // in package currency
                $discountNative = 0.0; // in package currency

                if ($promo) {
                    if ($promo->type === 'percent') {
                        $discountNative = round($priceNative * ($promo->value / 100), 2);
                    } else { // amount
                        $discountCurrency = $promo->currency ?: $package->currency;
                        if ($discountCurrency === $package->currency) {
                            $discountNative = (float) $promo->value;
                        } else {
                            $converted = CurrencyRate::convert((float) $promo->value, $discountCurrency, $package->currency);
                            if ($converted === null) {
                                throw new \RuntimeException('Missing conversion rate for promo amount');
                            }
                            $discountNative = (float) $converted;
                        }
                    }
                    // Cap discount to price
                    if ($discountNative > $priceNative) {
                        $discountNative = $priceNative;
                    }
                }

                // USD value of purchase after discount
                $amountUsd = CurrencyRate::toUsd($priceNative - $discountNative, $currency);

                // Amount to debit in wallet currency
                $debitAmount = $priceNative - $discountNative; // default when wallet currency equals package currency
                if ($wallet->currency !== $currency) {
                    $priceConverted = CurrencyRate::convert($priceNative, $currency, $wallet->currency);
                    $discountConverted = CurrencyRate::convert($discountNative, $currency, $wallet->currency);
                    if ($priceConverted === null || $discountConverted === null) {
                        throw new \RuntimeException('Wallet currency mismatch; missing conversion rate');
                    }
                    $debitAmount = (float) $priceConverted - (float) $discountConverted;
                }

                if ((float) $wallet->balance < (float) $debitAmount) {
                    throw new \RuntimeException('Insufficient balance');
                }

                // Deduct and record transaction
                $wallet->balance = bcsub((string) $wallet->balance, (string) $debitAmount, 2);
                $wallet->save();

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $debitAmount,
                    'amount_usd' => $amountUsd,
                    'type' => 'debit',
                    'description' => 'Package purchase: ' . $package->name,
                    'meta' => [
                        'related_service' => 'package_purchase',
                        'package_id' => $package->id,
                        'package_slug' => $package->slug,
                        'duration_days' => $package->duration_days,
                        'price_native' => [
                            'amount' => (float) $package->price,
                            'currency' => $currency,
                        ],
                        'discount_native' => [
                            'amount' => $discountNative,
                            'currency' => $currency,
                        ],
                        'wallet_currency' => $wallet->currency,
                        'promotion_code' => $promo ? $promo->code : null,
                    ],
                    'balance_after' => $wallet->balance,
                ]);

                // Activate user package
                \App\Models\UserPackage::create([
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'status' => 'active',
                    'activated_at' => now(),
                    'expires_at' => now()->addDays($package->duration_days),
                ]);

                // Optional: mirror on users table for quick reads
                $user->update(['user_package' => $package->slug]);

                // Track redemption and increment usage
                if ($promo) {
                    PromotionRedemption::create([
                        'promotion_id' => $promo->id,
                        'user_id' => $user->id,
                        'package_id' => $package->id,
                        'discount_amount' => $discountNative,
                        'discount_currency' => $currency,
                        'meta' => [
                            'wallet_currency' => $wallet->currency,
                            'debit_amount_wallet' => $debitAmount,
                        ],
                    ]);
                    $promo->increment('times_used');
                }
            });
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Plan activated: ' . $package->name);
    }
}