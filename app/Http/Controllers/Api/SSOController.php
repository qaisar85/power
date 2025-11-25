<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class SSOController extends Controller
{
    public function generateToken(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = $user->createToken('sso-token', ['*'], now()->addMinutes(5));

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'expires_at' => now()->addMinutes(5)->toISOString(),
        ]);
    }

    public function validateToken(Request $request)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        
        if (!$accessToken || $accessToken->expires_at < now()) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }

        return response()->json([
            'valid' => true,
            'user' => [
                'id' => $accessToken->tokenable->id,
                'name' => $accessToken->tokenable->name,
                'email' => $accessToken->tokenable->email,
                'roles' => $accessToken->tokenable->getRoleNames(),
            ],
        ]);
    }
}