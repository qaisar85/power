<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    /**
     * Get user's referral stats
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $stats = [
            'referral_code' => $user->referral_code,
            'total_referrals' => $user->referrals()->count(),
            'total_earnings' => $user->referrals()->sum('total_earned'),
            'referrals_by_level' => $user->referrals()
                ->selectRaw('level, count(*) as count, sum(total_earned) as earnings')
                ->groupBy('level')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get user's referral tree
     */
    public function tree(Request $request): JsonResponse
    {
        $referrals = $request->user()->referrals()
            ->with('referred:id,name,email,created_at')
            ->orderBy('level')
            ->orderBy('created_at')
            ->paginate(50);

        return response()->json($referrals);
    }

    /**
     * Generate referral code
     */
    public function generateCode(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->referral_code) {
            return response()->json(['message' => 'Referral code already exists'], 400);
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        $user->update(['referral_code' => $code]);

        return response()->json(['referral_code' => $code]);
    }

    /**
     * Register with referral code
     */
    public static function processReferral(User $newUser, string $referralCode): void
    {
        $referrer = User::where('referral_code', $referralCode)->first();
        
        if (!$referrer) {
            return;
        }

        $newUser->update(['referred_by' => $referrer->id]);

        // Commission rates for each level (8 levels)
        $commissionRates = [
            1 => 10.00, // 10% for direct referral
            2 => 5.00,  // 5% for level 2
            3 => 3.00,  // 3% for level 3
            4 => 2.00,  // 2% for level 4
            5 => 1.50,  // 1.5% for level 5
            6 => 1.00,  // 1% for level 6
            7 => 0.75,  // 0.75% for level 7
            8 => 0.50,  // 0.5% for level 8
        ];

        $currentReferrer = $referrer;
        $level = 1;

        // Create referral records for up to 8 levels
        while ($currentReferrer && $level <= 8) {
            Referral::create([
                'referrer_id' => $currentReferrer->id,
                'referred_id' => $newUser->id,
                'level' => $level,
                'commission_rate' => $commissionRates[$level],
            ]);

            $currentReferrer = $currentReferrer->referredBy;
            $level++;
        }
    }

    /**
     * Process commission payment
     */
    public static function processCommission(User $user, float $amount): void
    {
        $referrals = Referral::where('referred_id', $user->id)->get();

        foreach ($referrals as $referral) {
            $commission = ($amount * $referral->commission_rate) / 100;
            
            $referral->increment('total_earned', $commission);
            
            // Add to referrer's wallet (implement wallet logic)
            // $referral->referrer->wallet()->increment('balance', $commission);
        }
    }
}