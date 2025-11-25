<?php

namespace App\Http\Controllers;

use App\Models\FreelanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FreelanceServiceController extends Controller
{
    public function create()
    {
        return Inertia::render('Freelance/Services/Create', [
            'defaults' => [
                'currency' => 'USD',
                'price_type' => 'fixed',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $hasActivePackage = \App\Models\UserPackage::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if (! $hasActivePackage) {
            return redirect()->back()->with('error', 'An active subscription is required to publish services.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'subcategories' => ['nullable', 'array'],
            'price_type' => ['required', 'string'],
            'price_value' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'delivery_days' => ['nullable', 'integer', 'min:1'],
            'tags' => ['nullable', 'array'],
        ]);

        $slugBase = Str::slug($data['title']);
        $slug = $slugBase . '-' . Str::lower(Str::random(6));

        $service = FreelanceService::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'subcategories' => $data['subcategories'] ?? [],
            'price_type' => $data['price_type'],
            'price_value' => $data['price_value'],
            'currency' => $data['currency'],
            'delivery_days' => $data['delivery_days'] ?? null,
            'status' => 'pending',
            'tags' => $data['tags'] ?? [],
            'photos' => [],
            'packages' => [],
        ]);

        return redirect()->route('freelance.services.show', $service->slug)
            ->with('status', 'Service submitted for moderation.');
    }

    public function show(FreelanceService $service)
    {
        return Inertia::render('Freelance/Services/Show', [
            'service' => $service,
        ]);
    }
}
