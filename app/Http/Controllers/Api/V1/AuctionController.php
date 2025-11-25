<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuctionController extends Controller
{
    /**
     * Display auction listings
     */
    public function index(Request $request): JsonResponse
    {
        $query = Listing::where('type', 'auction')
            ->where('status', 'published');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $auctions = $query->with('user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($auctions);
    }

    /**
     * Display the specified auction
     */
    public function show(Listing $listing): JsonResponse
    {
        if ($listing->type !== 'auction' || $listing->status !== 'published') {
            return response()->json(['message' => 'Auction not found'], 404);
        }

        $listing->load(['user:id,name,email', 'bids' => function ($query) {
            $query->latest()->limit(5)->with('user:id,name');
        }]);

        return response()->json($listing);
    }

    /**
     * Place a bid on auction
     */
    public function placeBid(Request $request, Listing $listing): JsonResponse
    {
        if ($listing->type !== 'auction' || $listing->status !== 'published') {
            return response()->json(['message' => 'Auction not available'], 400);
        }

        if ($listing->user_id === $request->user()->id) {
            return response()->json(['message' => 'Cannot bid on your own auction'], 400);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $highestBid = $listing->bids()->max('amount') ?? 0;

        if ($request->amount <= $highestBid) {
            return response()->json(['message' => 'Bid must be higher than current highest bid'], 400);
        }

        $bid = Bid::create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
        ]);

        return response()->json([
            'message' => 'Bid placed successfully',
            'bid' => $bid->load('user:id,name')
        ], 201);
    }

    /**
     * List bids for auction
     */
    public function listBids(Listing $listing): JsonResponse
    {
        if ($listing->type !== 'auction') {
            return response()->json(['message' => 'Not an auction'], 400);
        }

        $bids = $listing->bids()
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($bids);
    }

    /**
     * Get user's bids
     */
    public function myBids(Request $request): JsonResponse
    {
        $bids = Bid::where('user_id', $request->user()->id)
            ->with('listing:id,title,type,status')
            ->latest()
            ->paginate(20);

        return response()->json($bids);
    }
}