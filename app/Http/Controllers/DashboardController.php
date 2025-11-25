<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\BusinessSector;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Listing;
use App\Models\Favorite;
use App\Models\UserPackage;
use App\Models\ListingRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Ensure role is selected (middleware should handle this, but double-check)
        if (!$user->role_selected) {
            return redirect()->route('role-selection');
        }
        
        $modules = Module::active()
            ->orderBy('sort_order')
            ->get()
            ->map(function ($module) use ($user) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'slug' => $module->slug,
                    'path' => $module->path,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'has_access' => $user->hasAccessToModule($module->slug),
                ];
            });

        $sectors = BusinessSector::active()
            ->with('subsectors')
            ->orderBy('sort_order')
            ->get();

        // Get user's primary role and permissions
        $primaryRole = $user->primary_role;
        $roles = $user->getRoleNames();

        return Inertia::render('Dashboard', [
            'modules' => $modules,
            'sectors' => $sectors,
            'user' => $user->load('companies'),
            'primaryRole' => $primaryRole,
            'roles' => $roles,
        ]);
    }

    public function account()
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $listingsCount = Listing::where('user_id', $user->id)->count();
        $favoritesCount = Favorite::where('user_id', $user->id)->count();
        $subscriptionsCount = UserPackage::where('user_id', $user->id)->where('status', 'active')->count();
        $messagesCount = 0; // Placeholder until chat/messages module is implemented

        return Inertia::render('Account/Index', [
            'user' => $user->only(['id','name','email']),
            'stats' => [
                'listingsCount' => $listingsCount,
                'favoritesCount' => $favoritesCount,
                'subscriptionsCount' => $subscriptionsCount,
                'messagesCount' => $messagesCount,
            ],
        ]);
    }

    public function messages()
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $inbox = ListingRequest::whereHas('listing', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['listing', 'user'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'type' => $r->type,
                    'listing_id' => $r->listing_id,
                    'listing_title' => optional($r->listing)->title,
                    'from_name' => optional($r->user)->name,
                    'status' => $r->status,
                    'created_at' => $r->created_at?->toDateTimeString(),
                ];
            });
    
        $outbox = ListingRequest::where('user_id', $user->id)
            ->with(['listing', 'user'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'type' => $r->type,
                    'listing_id' => $r->listing_id,
                    'listing_title' => optional($r->listing)->title,
                    'user_name' => optional($r->user)->name,
                    'status' => $r->status,
                    'created_at' => $r->created_at?->toDateTimeString(),
                ];
            });
    
        return Inertia::render('Account/Messages', [
            'inbox' => $inbox,
            'outbox' => $outbox,
        ]);
    }

    public function myListings()
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $listings = Listing::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'deal_type' => $l->deal_type,
                    'status' => $l->status,
                    'price' => $l->price,
                    'currency' => $l->currency ?? 'USD',
                    'created_at' => $l->created_at?->toDateTimeString(),
                ];
            });

        return Inertia::render('Account/Listings', [
            'listings' => $listings,
        ]);
    }

    public function settings()
    {
        $user = auth()->user();
        abort_unless($user, 401);

        return Inertia::render('Account/Settings', [
            'user' => $user->only(['id', 'name', 'email']),
            'profileUrl' => '/user/profile',
            'billingUrl' => route('packages.index'),
        ]);
    }
}