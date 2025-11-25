<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\KycVerification;
use App\Models\ShareHolding;
use App\Models\ShareTransaction;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeCheckoutSession;

class StocksController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Stocks/Index', [
            'disclaimer' => 'This is not a public offer. Please read all documents carefully before purchasing.',
        ]);
    }

    public function buy(Request $request)
    {
        $user = Auth::user();
        $kyc = $user ? KycVerification::where('user_id', $user->id)->first() : null;
        return Inertia::render('Stocks/Buy', [
            'kyc_status' => $kyc ? $kyc->status : 'none',
            'needs_kyc' => !$kyc || $kyc->status !== 'approved',
            'disclaimer' => 'This is not a public offer. Please read all documents carefully before purchasing.',
        ]);
    }

    public function storeBuy(Request $request)
    {
        $request->validate([
            'shares' => ['required','integer','min:1'],
            'payment_method' => ['required','in:stripe,yookassa,crypto,bank'],
            'agree_terms' => ['accepted'],
        ], [
            'agree_terms.accepted' => 'You must accept the Investment Agreement before purchasing.',
        ]);

        $user = $request->user();
        $kyc = KycVerification::where('user_id', $user->id)->first();
        if (!$kyc || $kyc->status !== 'approved') {
            return back()->withErrors(['kyc' => 'Please complete KYC before purchasing shares.'])->withInput();
        }

        $pricePerShare = config('services.stocks.price_per_share', 100);
        $shares = (int)$request->input('shares');
        $amount = $shares * $pricePerShare;

        $tx = ShareTransaction::create([
            'user_id' => $user->id,
            'type' => 'buy',
            'shares' => $shares,
            'price_per_share' => $pricePerShare,
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => $request->input('payment_method'),
            'payment_reference' => null,
        ]);

        // If Stripe is selected, create a Checkout session and redirect to it
        if ($request->input('payment_method') === 'stripe') {
            Stripe::setApiKey(config('services.stripe.secret'));

            $successUrl = route('investor.dashboard') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = route('stocks.buy');

            $session = StripeCheckoutSession::create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Share Purchase',
                            'description' => "Buy {$shares} shares at \${$pricePerShare}/share",
                        ],
                        'unit_amount' => (int) round($pricePerShare * 100),
                    ],
                    'quantity' => $shares,
                ]],
                'metadata' => [
                    'type' => 'share_purchase',
                    'user_id' => (string) $user->id,
                    'share_transaction_id' => (string) $tx->id,
                    'amount' => (string) $amount,
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            $tx->payment_reference = $session->id;
            $tx->save();

            return \Illuminate\Support\Facades\Redirect::away($session->url);
        }

        // For non-card methods, keep user on dashboard and await offsite confirmation
        return redirect()->route('investor.dashboard')->with('status', 'Purchase initiated. Awaiting payment confirmation.');
    }

    public function cancelBuy(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('stocks.buy')->with('error', 'Purchase canceled.');
        }

        $tx = ShareTransaction::where('payment_reference', $sessionId)
            ->where('status', 'pending')
            ->first();

        if ($tx) {
            $tx->status = 'canceled';
            $tx->save();
            return redirect()->route('stocks.buy')->with('error', 'Card payment canceled. No charges applied.');
        }

        return redirect()->route('stocks.buy')->with('status', 'No pending purchase found for cancellation.');
    }
}