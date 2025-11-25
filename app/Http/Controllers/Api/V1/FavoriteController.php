<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    /**
     * Display user's favorites
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $request->user()->favorites()
            ->with('listing:id,title,price,currency,location,photos')
            ->latest()
            ->paginate(20);

        return response()->json($favorites);
    }

    /**
     * Add listing to favorites
     */
    public function store(Request $request, Listing $listing): JsonResponse
    {
        $favorite = $request->user()->favorites()
            ->where('listing_id', $listing->id)
            ->first();

        if ($favorite) {
            return response()->json(['message' => 'Listing is already in favorites'], 400);
        }

        $request->user()->favorites()->create([
            'listing_id' => $listing->id,
        ]);

        return response()->json(['message' => 'Listing added to favorites'], 201);
    }

    /**
     * Remove listing from favorites
     */
    public function destroy(Request $request, Listing $listing): JsonResponse
    {
        $favorite = $request->user()->favorites()
            ->where('listing_id', $listing->id)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Listing not found in favorites'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Listing removed from favorites']);
    }

    /**
     * Check if listing is in favorites
     */
    public function check(Request $request, Listing $listing): JsonResponse
    {
        $isFavorite = $request->user()->favorites()
            ->where('listing_id', $listing->id)
            ->exists();

        return response()->json(['is_favorite' => $isFavorite]);
    }
}