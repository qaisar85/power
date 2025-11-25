<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.recaptcha.secret');
        if (! $secret) {
            return $next($request);
        }

        $token = $request->input('recaptcha_token') ?? $request->input('g-recaptcha-response');
        if (! $token) {
            return response()->json(['error' => 'Recaptcha token missing'], 422);
        }

        $res = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        $ok = $res->ok() && (bool) ($res->json('success') ?? false);
        if (! $ok) {
            return response()->json(['error' => 'Recaptcha verification failed'], 422);
        }

        return $next($request);
    }
}

