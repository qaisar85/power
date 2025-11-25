<?php

namespace App\Services;

use App\Models\User;
use App\Http\Controllers\Api\V1\ReferralController;
use Illuminate\Support\Str;

class RegistrationService
{
    public static function registerUser(array $userData, ?string $referralCode = null): User
    {
        // Generate referral code for new user
        do {
            $newReferralCode = strtoupper(Str::random(8));
        } while (User::where('referral_code', $newReferralCode)->exists());

        $userData['referral_code'] = $newReferralCode;

        $user = User::create($userData);

        // Process referral if code provided
        if ($referralCode) {
            ReferralController::processReferral($user, $referralCode);
        }

        return $user;
    }
}