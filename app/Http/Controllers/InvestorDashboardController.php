<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\ShareHolding;
use App\Models\ShareTransaction;
use App\Models\KycVerification;

class InvestorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $holding = ShareHolding::firstOrCreate(['user_id' => $user->id], ['shares' => 0]);
        $transactions = ShareTransaction::where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(25)
            ->get();
        $kyc = KycVerification::where('user_id', $user->id)->first();

        // Pull recent notifications (database) for dashboard widget
        $notifications = $user->notifications()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? ($n->type ?? 'notification'),
                    'data' => $n->data ?? [],
                    'created_at' => optional($n->created_at)->toDateTimeString(),
                    'read_at' => optional($n->read_at)->toDateTimeString(),
                ];
            });

        return Inertia::render('InvestorDashboard/Index', [
            'holding' => $holding,
            'transactions' => $transactions,
            'kyc_status' => $kyc ? $kyc->status : 'none',
            'disclaimer' => 'This is not a public offer. Please read all documents carefully before purchasing.',
            'notifications' => $notifications,
        ]);
    }
}