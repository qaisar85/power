<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class WalletController extends Controller
{
    // Return JSON list of available payment methods (active by default)
    public function paymentMethods(Request $request)
    {
        $onlyActive = $request->boolean('active', true);

        $query = PaymentMethod::query()->orderBy('sort_order');
        if ($onlyActive) {
            $query->where('is_active', true);
        }

        $methods = $query->get(['id', 'name', 'type', 'config', 'is_active', 'sort_order']);

        return response()->json($methods);
    }

    // Minimal stubs for other wallet routes so existing routes don't break
    public function balance(Request $request)
    {
        $user = $request->user();
        $balance = $user && method_exists($user, 'wallet') ? optional($user->wallet)->amount : 0;
        return response()->json(['balance' => $balance]);
    }

    public function transactions(Request $request)
    {
        $user = $request->user();
        $transactions = [];
        if ($user && method_exists($user, 'transactions')) {
            $transactions = $user->transactions()->latest()->limit(50)->get();
        }
        return response()->json($transactions);
    }

    public function topup(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'string', 'in:stripe,paypal,bank_transfer,crypto'],
            'currency' => ['nullable', 'string', 'max:8'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $currency = $data['currency'] ?? 'USD';

        $pr = \App\Models\PaymentRequest::create([
            'user_id' => $user->id,
            'amount' => (float) $data['amount'],
            'currency' => $currency,
            'method' => $data['method'],
            'status' => 'pending',
            'reference' => $data['reference'] ?? null,
            'meta' => [
                'created_via' => 'api',
            ],
        ]);

        return response()->json([
            'ok' => true,
            'payment_request_id' => $pr->id,
            'status' => $pr->status,
        ], 201);
    }

    public function transfer(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'to_user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        if ((int) $data['to_user_id'] === (int) $user->id) {
            return response()->json(['error' => 'Cannot transfer to self'], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $data) {
            $fromWallet = \App\Models\Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'USD']
            );
            $toUser = \App\Models\User::find($data['to_user_id']);
            $toWallet = \App\Models\Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $toUser->id],
                ['balance' => 0, 'currency' => $fromWallet->currency]
            );

            $amount = (float) $data['amount'];
            if ((float) $fromWallet->balance < $amount) {
                throw new \RuntimeException('Insufficient balance');
            }

            $fromWallet->balance = bcsub((string) $fromWallet->balance, (string) $amount, 2);
            $fromWallet->save();

            $toWallet->balance = bcadd((string) $toWallet->balance, (string) $amount, 2);
            $toWallet->save();

            $amountUsd = \App\Models\CurrencyRate::toUsd($amount, $fromWallet->currency);

            \App\Models\WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'amount_usd' => $amountUsd,
                'type' => 'debit',
                'description' => 'Wallet transfer to user '.$toUser->id,
                'meta' => [ 'to_user_id' => $toUser->id ],
                'balance_after' => $fromWallet->balance,
            ]);

            \App\Models\WalletTransaction::create([
                'user_id' => $toUser->id,
                'amount' => $amount,
                'amount_usd' => $amountUsd,
                'type' => 'credit',
                'description' => 'Wallet transfer from user '.$user->id,
                'meta' => [ 'from_user_id' => $user->id ],
                'balance_after' => $toWallet->balance,
            ]);
        });

        return response()->json(['ok' => true]);
    }

    public function withdraw(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $kyc = \App\Models\KycVerification::where('user_id', $user->id)->first();
        if (! $kyc || $kyc->status !== 'approved') {
            return response()->json(['error' => 'KYC approval required for withdrawals'], 422);
        }

        $data = $request->validate([
            'amount' => ['required','numeric','min:1'],
            'currency' => ['nullable','string','max:8'],
            'method' => ['required','string','in:bank_transfer,paypal,stripe,crypto'],
            'destination' => ['required','string','max:255'],
        ]);

        $currency = $data['currency'] ?? 'USD';

        $wallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => $currency]
        );

        if ((float) $wallet->balance < (float) $data['amount']) {
            return response()->json(['error' => 'Insufficient balance'], 422);
        }

        $pr = \App\Models\PaymentRequest::create([
            'user_id' => $user->id,
            'amount' => (float) $data['amount'],
            'currency' => $currency,
            'method' => $data['method'],
            'status' => 'pending',
            'reference' => null,
            'meta' => [
                'type' => 'withdrawal',
                'destination' => $data['destination'],
            ],
        ]);

        return response()->json(['ok' => true, 'payment_request_id' => $pr->id], 201);
    }
}
