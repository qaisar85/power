<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // If role is already selected, redirect to dashboard
        if ($user->role_selected && !$request->routeIs('role-selection')) {
            return redirect()->route('dashboard');
        }

        // If role not selected and not on role selection page, allow access
        if (!$user->role_selected) {
            return $next($request);
        }

        return $next($request);
    }
}
