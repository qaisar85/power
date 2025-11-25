<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FavoritesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $favorites = Favorite::where('user_id', $user->id)
            ->with(['listing.user'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($fav) {
                $l = $fav->listing;
                return [
                    'id' => $fav->id,
                    'listing_id' => $l->id,
                    'title' => $l->title,
                    'price' => $l->price,
                    'currency' => $l->currency,
                    'location' => $l->location,
                    'category' => $l->category,
                    'subcategories' => $l->subcategories ?? [],
                    'photos' => $l->photos ?? [],
                    'status' => $l->status,
                    'deal_type' => $l->deal_type,
                    'created_at' => $fav->created_at?->toDateTimeString(),
                    'seller' => [
                        'name' => $l->user->name,
                    ],
                ];
            });

        return Inertia::render('Account/Favorites', [
            'favorites' => $favorites,
        ]);
    }

    public function store(Request $request, Listing $listing)
    {
        $user = $request->user();
        abort_unless($user, 401);

        Favorite::firstOrCreate([
            'user_id' => $user->id,
            'listing_id' => $listing->id,
        ]);

        return back()->with('success', 'Added to favorites');
    }

    public function destroy(Request $request, Listing $listing)
    {
        $user = $request->user();
        abort_unless($user, 401);

        Favorite::where('user_id', $user->id)
            ->where('listing_id', $listing->id)
            ->delete();

        return back()->with('success', 'Removed from favorites');
    }
}