<?php

namespace App\Http\Controllers;

use App\Models\PlatformFee;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminPlatformFeeController extends Controller
{
    public function index(Request $request)
    {
        $fees = PlatformFee::orderByDesc('id')->paginate(30);
        $pct = Setting::get('platform_fee_percentage');
        return Inertia::render('Admin/Finance/PlatformFees', [
            'fees' => $fees,
            'platform_fee_percentage' => $pct,
        ]);
    }

    public function collect(Request $request, PlatformFee $fee)
    {
        if ($fee->status === 'collected') {
            return back()->with('success', 'Already collected');
        }

        DB::transaction(function () use ($fee) {
            $platformUserId = (int) (Setting::get('platform_wallet_user_id') ?? 0);
            if ($platformUserId > 0) {
                $wallet = \App\Models\Wallet::lockForUpdate()->firstOrCreate(
                    ['user_id' => $platformUserId],
                    ['balance' => 0, 'currency' => $fee->currency]
                );

                $creditAmount = (float) $fee->amount;
                $wallet->balance = bcadd((string) $wallet->balance, (string) $creditAmount, 2);
                $wallet->save();

                \App\Models\WalletTransaction::create([
                    'user_id' => $platformUserId,
                    'amount' => $creditAmount,
                    'amount_usd' => \App\Models\CurrencyRate::toUsd($creditAmount, $fee->currency),
                    'type' => 'credit',
                    'description' => 'Platform fee collected for order '.$fee->order_id,
                    'meta' => [ 'platform_fee_id' => $fee->id ],
                    'balance_after' => $wallet->balance,
                ]);
            }

            $fee->status = 'collected';
            $fee->collected_at = now();
            $fee->save();
        });

        return back()->with('success', 'Platform fee marked collected');
    }
}
