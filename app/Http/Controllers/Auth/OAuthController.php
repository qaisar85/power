<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    /**
     * Redirect to OAuth provider.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Authentication failed. Please try again.');
        }

        // Find or create user
        $user = $this->findOrCreateUser($provider, $socialUser);

        // Mark email as verified if from OAuth
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        // Update last login
        $user->last_login_at = now();
        $user->save();

        Auth::login($user, true);

        // Redirect to role selection if role not selected
        if (!$user->role_selected) {
            return redirect()->route('role-selection');
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Find or create user from OAuth data.
     */
    protected function findOrCreateUser(string $provider, $socialUser): User
    {
        // Try to find user by OAuth ID
        $user = User::where('oauth_provider', $provider)
            ->where('oauth_id', $socialUser->getId())
            ->first();

        if ($user) {
            return $user;
        }

        // Try to find user by email
        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Link OAuth account to existing user
            $user->oauth_provider = $provider;
            $user->oauth_id = $socialUser->getId();
            $user->save();

            return $user;
        }

        // Create new user
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(32)), // Random password for OAuth users
            'oauth_provider' => $provider,
            'oauth_id' => $socialUser->getId(),
            'email_verified_at' => now(),
            'user_type' => 'individual',
            'is_active' => true,
        ]);

        // Assign basic role
        try {
            $user->assignRole('Company');
        } catch (\Throwable $e) {
            // Ignore if role not yet seeded
        }

        return $user;
    }

    /**
     * Validate OAuth provider.
     */
    protected function validateProvider(string $provider): void
    {
        if (!in_array($provider, ['google', 'linkedin', 'facebook'])) {
            abort(404);
        }
    }
}
