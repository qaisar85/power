<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Accepts email/password, returns JSON with token and user, and sets HttpOnly cookie for subdomain use.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        Auth::login($user, (bool) $request->boolean('remember'));

        // Issue short-lived token for SSO/API usage
        $accessToken = $user->createToken('central-auth', ['*'], now()->addMinutes(60));
        $token = $accessToken->plainTextToken;

        // HttpOnly cookie for cross-subdomain usage
        $cookieDomain = config('session.domain');
        $secure = config('session.secure', true);
        $minutes = 60;
        $cookie = Cookie::make(
            'central_token',
            $token,
            $minutes,
            '/',
            $cookieDomain,
            $secure,
            true, // httpOnly
            false, // raw
            'None' // SameSite
        );

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ])->cookie($cookie);
    }

    /**
     * POST /api/auth/refresh
     * Rotates and returns a new token; invalidates previous if provided.
     */
    public function refresh(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Optionally delete previous token if provided
        $oldTokenPlain = $request->string('token')->toString();
        if ($oldTokenPlain) {
            $old = PersonalAccessToken::findToken($oldTokenPlain);
            if ($old && $old->tokenable_id === $user->id) {
                $old->delete();
            }
        }

        $accessToken = $user->createToken('central-auth', ['*'], now()->addMinutes(60));
        $token = $accessToken->plainTextToken;

        $cookieDomain = config('session.domain');
        $secure = config('session.secure', true);
        $minutes = 60;
        $cookie = Cookie::make('central_token', $token, $minutes, '/', $cookieDomain, $secure, true, false, 'None');

        return response()->json(['token' => $token])->cookie($cookie);
    }

    /**
     * POST /api/auth/verify-token
     * Accepts Bearer token or token param, returns user profile.
     */
    public function verifyToken(Request $request)
    {
        $token = $request->bearerToken() ?: $request->string('token')->toString() ?: $request->cookie('central_token');

        if (! $token) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (! $accessToken || ($accessToken->expires_at && $accessToken->expires_at < now())) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        $user = $accessToken->tokenable;
        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * POST /api/auth/logout
     * Revokes current token and logs out.
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken() ?: $request->cookie('central_token');
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $accessToken->delete();
            }
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true])->withoutCookie('central_token');
    }
}