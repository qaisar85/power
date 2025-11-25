<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Package;
use App\Models\UserPackage;
use App\Models\CurrencyRate;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFinanceController extends Controller
{
    public function index(Request $request)
    {
        // Aggregate metrics
        $totalBalances = (float) Wallet::sum('balance');
        $totalDebits = (float) WalletTransaction::where('type', 'debit')
            ->sum(DB::raw('COALESCE(amount_usd, amount)'));
        $totalCredits = (float) WalletTransaction::where('type', 'credit')
            ->sum(DB::raw('COALESCE(amount_usd, amount)'));
        $activePackagesCount = (int) UserPackage::where('status', 'active')->count();

        $transactions = WalletTransaction::with('user')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $packages = Package::orderBy('price')->get();
        $rates = CurrencyRate::orderBy('currency')->get();

        $pendingManualPayments = \App\Models\PaymentRequest::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $promotions = Promotion::orderByDesc('created_at')->get();

        return Inertia::render('Admin/Finance/Index', [
            'metrics' => [
                'totalRevenue' => $totalDebits,
                'totalDebits' => $totalDebits,
                'totalCredits' => $totalCredits,
                'totalBalances' => $totalBalances,
                'activePackagesCount' => $activePackagesCount,
            ],
            'packages' => $packages,
            'rates' => $rates,
            'transactions' => $transactions,
            'pendingManualPayments' => $pendingManualPayments,
            'promotions' => $promotions,
        ]);
    }

    public function approvePayment(Request $request, \App\Models\PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Payment request already processed.');
        }

        DB::transaction(function () use ($paymentRequest) {
            $user = $paymentRequest->user;
            $wallet = Wallet::lockForUpdate()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => $paymentRequest->currency]
            );

            // Convert to wallet currency if needed
            $creditAmount = (float) $paymentRequest->amount;
            if ($wallet->currency !== $paymentRequest->currency) {
                $converted = CurrencyRate::convert($creditAmount, $paymentRequest->currency, $wallet->currency);
                if ($converted !== null) {
                    $creditAmount = $converted;
                }
            }
            $amountUsd = CurrencyRate::toUsd((float) $paymentRequest->amount, $paymentRequest->currency);

            $wallet->balance = bcadd((string) $wallet->balance, (string) $creditAmount, 2);
            $wallet->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'amount' => $creditAmount,
                'amount_usd' => $amountUsd,
                'type' => 'credit',
                'description' => 'Manual payment approved (' . $paymentRequest->method . ')',
                'meta' => [
                    'method' => $paymentRequest->method,
                    'payment_request_id' => $paymentRequest->id,
                    'original_amount' => (float) $paymentRequest->amount,
                    'original_currency' => $paymentRequest->currency,
                    'reference' => $paymentRequest->reference,
                ],
                'balance_after' => $wallet->balance,
            ]);

            $paymentRequest->status = 'approved';
            $paymentRequest->save();
        });

        return redirect()->route('finance.index')->with('success', 'Payment approved and wallet credited.');
    }

    public function rejectPayment(Request $request, \App\Models\PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Payment request already processed.');
        }
        $paymentRequest->status = 'rejected';
        $paymentRequest->save();
        return redirect()->route('finance.index')->with('success', 'Payment request rejected.');
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:8'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'features' => ['nullable'], // JSON or array
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = str($data['name'])->slug('-');
        }

        if (!empty($data['features']) && is_string($data['features'])) {
            try {
                $decoded = json_decode($data['features'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['features'] = $decoded;
                } else {
                    $data['features'] = [];
                }
            } catch (\Throwable $e) {
                $data['features'] = [];
            }
        }

        Package::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'currency' => $data['currency'],
            'duration_days' => $data['duration_days'],
            'features' => $data['features'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('finance.index')->with('success', 'Package created');
    }

    public function storeRate(Request $request)
    {
        $data = $request->validate([
            'currency' => ['required', 'string', 'max:8'],
            'usd_rate' => ['required', 'numeric', 'min:0.000001'],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        $currency = strtoupper(trim($data['currency']));

        CurrencyRate::updateOrCreate(
            ['currency' => $currency],
            [
                'usd_rate' => $data['usd_rate'],
                'source' => $data['source'] ?? null,
                'fetched_at' => now(),
            ]
        );

        return redirect()->route('finance.index')->with('success', 'Rate saved');
    }

    public function storePromotion(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'type' => ['required', 'in:percent,amount'],
            'value' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:3'],
            'applies_to_package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date'],
            'active' => ['sometimes', 'boolean'],
        ]);

        // Normalize code
        $data['code'] = strtoupper(trim($data['code']));
        if (empty($data['currency'])) {
            $data['currency'] = null;
        }

        Promotion::updateOrCreate(
            ['code' => $data['code']],
            [
                'type' => $data['type'],
                'value' => (float) $data['value'],
                'currency' => $data['currency'],
                'applies_to_package_id' => $data['applies_to_package_id'] ?? null,
                'max_uses' => $data['max_uses'] ?? null,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_to' => $data['valid_to'] ?? null,
                'active' => $data['active'] ?? true,
                'meta' => [],
            ]
        );

        return redirect()->route('finance.index')->with('success', 'Promotion saved');
    }
}