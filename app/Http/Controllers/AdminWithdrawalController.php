<?php

namespace App\Http\Controllers;

use App\Models\PaymentRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\CurrencyRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $items = PaymentRequest::where('status', 'pending')
            ->whereJsonContains('meta->type', 'withdrawal')
            ->orderByDesc('id')
            ->paginate(30);
        return Inertia::render('Admin/Finance/Withdrawals', [
            'withdrawals' => $items,
        ]);
    }

    public function approve(Request $request, PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->status !== 'pending') {
            return back()->with('error', 'Already processed');
        }

        DB::transaction(function () use ($paymentRequest) {
            $user = $paymentRequest->user;
            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => $paymentRequest->currency]
            );

            $debitAmount = (float) $paymentRequest->amount;
            if ($wallet->currency !== $paymentRequest->currency) {
                $converted = CurrencyRate::convert($debitAmount, $paymentRequest->currency, $wallet->currency);
                if ($converted !== null) {
                    $debitAmount = $converted;
                }
            }
            $amountUsd = CurrencyRate::toUsd((float) $paymentRequest->amount, $paymentRequest->currency);

            if ((float) $wallet->balance < $debitAmount) {
                throw new \RuntimeException('Insufficient wallet balance');
            }

            $wallet->balance = bcsub((string) $wallet->balance, (string) $debitAmount, 2);
            $wallet->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $debitAmount,
                'amount_usd' => $amountUsd,
                'type' => 'debit',
                'description' => 'Withdrawal payout ('.$paymentRequest->method.')',
                'meta' => [
                    'payment_request_id' => $paymentRequest->id,
                    'destination' => $paymentRequest->meta['destination'] ?? null,
                ],
                'balance_after' => $wallet->balance,
            ]);

            $paymentRequest->status = 'approved';
            $paymentRequest->save();
        });

        return back()->with('success', 'Withdrawal approved');
    }

    public function reject(Request $request, PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->status !== 'pending') {
            return back()->with('error', 'Already processed');
        }
        $paymentRequest->status = 'rejected';
        $paymentRequest->save();
        return back()->with('success', 'Withdrawal rejected');
    }
}

