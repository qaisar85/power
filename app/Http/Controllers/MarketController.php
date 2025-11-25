<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\SellOffer;
use App\Models\KycVerification;

class MarketController extends Controller
{
    public function index(Request $request)
    {
        $offers = SellOffer::where('status', 'open')
            ->orderBy('price_per_share')
            ->limit(50)
            ->get();
        return Inertia::render('Stocks/Market', [
            'offers' => $offers,
            'disclaimer' => 'This is not a public offer. Please read all documents carefully before purchasing.',
        ]);
    }

    public function storeOffer(Request $request)
    {
        $request->validate([
            'shares' => ['required','integer','min:1'],
            'price_per_share' => ['required','numeric','min:0.01'],
        ]);
        $user = $request->user();
        $kyc = KycVerification::where('user_id', $user->id)->first();
        if (!$kyc || $kyc->status !== 'approved') {
            return back()->withErrors(['kyc' => 'Please complete KYC before creating sell offers.'])->withInput();
        }
        SellOffer::create([
            'user_id' => $user->id,
            'shares' => (int)$request->input('shares'),
            'price_per_share' => (float)$request->input('price_per_share'),
            'status' => 'open',
        ]);
        return redirect()->route('stocks.market')->with('status', 'Sell offer created.');
    }
}