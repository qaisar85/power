<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    public function index()
    {
        $modules = Module::orderBy('sort_order')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'slug' => $m->slug,
                    'path' => $m->path,
                    'integration_type' => $m->integration_type,
                    'is_active' => $m->is_active,
                    'requires_auth' => $m->requires_auth,
                    'config' => $m->config,
                    'admin_url' => $m->config['admin_url'] ?? null,
                ];
            });

        return Inertia::render('Admin/Index', [
            'modules' => $modules,
        ]);
    }

    public function connect(string $slug): RedirectResponse
    {
        $module = Module::where('slug', $slug)->firstOrFail();
        $adminUrl = $module->config['admin_url'] ?? null;

        if (!$adminUrl) {
            return redirect()->back()->with('status', 'Admin URL not configured for this module.');
        }

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Generate a short-lived SSO token
        $token = $user->createToken('sso-admin', ['*'], now()->addMinutes(5));
        $plainToken = $token->plainTextToken;

        // Redirect to module admin with token
        $url = rtrim($adminUrl, '/').'?token='.urlencode($plainToken);

        return redirect($url);
    }
}