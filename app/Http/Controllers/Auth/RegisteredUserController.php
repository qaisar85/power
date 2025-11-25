<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:32',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'recaptcha_token' => ['nullable','string'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Assign basic role if available
        try {
            $user->assignRole('Company');
        } catch (\Throwable $e) {
            // ignore if role not yet seeded
        }

        event(new Registered($user));

        Auth::login($user);

        // Redirect to role selection if role not selected
        if (!$user->role_selected) {
            return redirect(route('role-selection', absolute: false));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
