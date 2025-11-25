<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FreelanceOrder;
use App\Models\FreelanceService;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CurrencyRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FreelanceOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $request->query('role', 'buyer');
        $query = FreelanceOrder::query();
        if ($role === 'seller') {
            $query->where('seller_id', $user->id);
        } else {
            $query->where('buyer_id', $user->id);
        }
        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request, FreelanceService $service)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'package' => ['nullable','string','max:50'],
        ]);

        $amount = (float) ($service->price_value ?? 0);
        $currency = $service->currency ?? 'USD';
        $platformFeePct = (float) (\App\Models\Setting::get('platform_fee_percentage', 5.0));
        $platformFee = round(($amount * $platformFeePct) / 100, 2);
        $sellerAmountBase = round($amount - $platformFee, 2);

        $order = null;

        DB::transaction(function () use ($user, $service, $amount, $currency, $data, $platformFeePct, $platformFee, &$order) {
            $buyerWallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => $currency]
            );

            $sellerWallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $service->user_id],
                ['balance' => 0, 'currency' => $currency]
            );

            $debitAmount = $amount;
            if ($buyerWallet->currency !== $currency) {
                $converted = CurrencyRate::convert($amount, $currency, $buyerWallet->currency);
                if ($converted === null) {
                    throw new \RuntimeException('Missing conversion rate');
                }
                $debitAmount = (float) $converted;
            }

            if ((float) $buyerWallet->balance < (float) $debitAmount) {
                throw new \RuntimeException('Insufficient balance');
            }

            $buyerWallet->balance = bcsub((string) $buyerWallet->balance, (string) $debitAmount, 2);
            $buyerWallet->save();

            $creditAmount = $sellerAmountBase ?? 0;
            if ($sellerWallet->currency !== $currency) {
                $converted = CurrencyRate::convert($creditAmount, $currency, $sellerWallet->currency);
                $creditAmount = (float) ($converted ?? $creditAmount);
            }
            $sellerWallet->balance = bcadd((string) $sellerWallet->balance, (string) $creditAmount, 2);
            $sellerWallet->save();

            $amountUsd = CurrencyRate::toUsd($amount, $currency);

            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $debitAmount,
                'amount_usd' => $amountUsd,
                'type' => 'debit',
                'description' => 'Purchase service '.$service->id,
                'meta' => [ 'service_id' => $service->id ],
                'balance_after' => $buyerWallet->balance,
            ]);

            WalletTransaction::create([
                'user_id' => $service->user_id,
                'amount' => $creditAmount,
                'amount_usd' => $amountUsd,
                'type' => 'credit',
                'description' => 'Service sold '.$service->id,
                'meta' => [ 'buyer_id' => $user->id, 'platform_fee_pct' => \App\Models\Setting::get('platform_fee_percentage', 5.0), 'platform_fee_native' => $platformFee ],
                'balance_after' => $sellerWallet->balance,
            ]);

            $order = FreelanceOrder::create([
                'service_id' => $service->id,
                'buyer_id' => $user->id,
                'seller_id' => $service->user_id,
                'package' => $data['package'] ?? null,
                'amount' => $amount,
                'platform_fee' => $platformFee ?? 0,
                'seller_amount' => $sellerAmountBase ?? 0,
                'currency' => $currency,
                'status' => 'paid',
            ]);

            \App\Models\PlatformFee::create([
                'order_id' => $order->id,
                'amount' => $platformFee ?? 0,
                'currency' => $currency,
                'status' => 'pending',
                'meta' => [ 'platform_fee_pct' => \App\Models\Setting::get('platform_fee_percentage', 5.0) ],
            ]);
        });

        return response()->json($order, 201);
    }

    public function refund(Request $request, FreelanceOrder $order)
    {
        $user = $request->user();
        if (! $user || $order->buyer_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($order->status !== 'paid') {
            return response()->json(['error' => 'Order not refundable'], 422);
        }

        $data = $request->validate([
            'reason' => ['required','string','max:2000'],
        ]);

        DB::transaction(function () use ($order, $data) {
            $buyerWallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $order->buyer_id],
                ['balance' => 0, 'currency' => $order->currency]
            );
            $sellerWallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $order->seller_id],
                ['balance' => 0, 'currency' => $order->currency]
            );

            if ((float) $sellerWallet->balance < (float) $order->amount) {
                throw new \RuntimeException('Seller balance insufficient for refund');
            }

            $sellerWallet->balance = bcsub((string) $sellerWallet->balance, (string) $order->amount, 2);
            $sellerWallet->save();

            $buyerWallet->balance = bcadd((string) $buyerWallet->balance, (string) $order->amount, 2);
            $buyerWallet->save();

            $amountUsd = CurrencyRate::toUsd((float) $order->amount, $order->currency);

            WalletTransaction::create([
                'user_id' => $order->seller_id,
                'amount' => $order->amount,
                'amount_usd' => $amountUsd,
                'type' => 'debit',
                'description' => 'Refund for order '.$order->order_id,
                'meta' => [ 'order_id' => $order->id ],
                'balance_after' => $sellerWallet->balance,
            ]);

            WalletTransaction::create([
                'user_id' => $order->buyer_id,
                'amount' => $order->amount,
                'amount_usd' => $amountUsd,
                'type' => 'credit',
                'description' => 'Refund received for order '.$order->order_id,
                'meta' => [ 'order_id' => $order->id ],
                'balance_after' => $buyerWallet->balance,
            ]);

            $order->status = 'refunded';
            $order->refund_reason = $data['reason'];
            $order->save();
        });

        return response()->json(['ok' => true]);
    }
}
