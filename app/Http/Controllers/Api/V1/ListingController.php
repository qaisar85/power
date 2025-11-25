<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ListingController extends Controller
{
    /**
     * Display a listing of authenticated user's listings
     */
    public function index(Request $request): JsonResponse
    {
        $listings = Listing::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($listings);
    }

    /**
     * Display public listings (for marketplace)
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $query = Listing::where('status', 'published');

        // Filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('currency')) {
            $query->where('currency', $request->currency);
        }

        // Search
        if ($request->has('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $listings = $query->with('user:id,name,email')
            ->paginate($request->get('per_page', 20));

        return response()->json($listings);
    }

    /**
     * Display the specified listing
     */
    public function show(Listing $listing): JsonResponse
    {
        $this->authorize('view', $listing);
        
        $listing->load(['user:id,name,email,phone']);
        
        return response()->json($listing);
    }

    /**
     * Display public listing
     */
    public function publicShow(Listing $listing): JsonResponse
    {
        if ($listing->status !== 'published') {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        $listing->load(['user:id,name,email']);
        
        // Increment view count (implement if needed)
        // $listing->increment('views_count');

        return response()->json($listing);
    }

    /**
     * Store a newly created listing
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:product,service,vacancy,news,tender,auction',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'subcategories' => 'nullable|array',
            'photos' => 'nullable|array|max:10',
            'photos.*' => 'string', // URLs or base64
            'deal_type' => 'nullable|in:sale,rent,auction',
            'payment_options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $listing = $request->user()->listings()->create(array_merge(
            $validator->validated(),
            ['status' => 'draft']
        ));

        return response()->json($listing, 201);
    }

    /**
     * Update the specified listing
     */
    public function update(Request $request, Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|in:product,service,vacancy,news,tender,auction',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'subcategories' => 'nullable|array',
            'photos' => 'nullable|array|max:10',
            'deal_type' => 'nullable|in:sale,rent,auction',
            'payment_options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $listing->update($validator->validated());

        return response()->json($listing);
    }

    /**
     * Publish a listing
     */
    public function publish(Listing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        if ($listing->status === 'published') {
            return response()->json(['message' => 'Listing is already published'], 400);
        }

        // Check if user has active package or credits
        // Implement package validation here

        $listing->update(['status' => 'under_review']);

        return response()->json([
            'message' => 'Listing submitted for review',
            'listing' => $listing
        ]);
    }

    /**
     * Remove the specified listing
     */
    public function destroy(Listing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);

        $listing->delete();

        return response()->json(['message' => 'Listing deleted successfully']);
    }

    /**
     * Request information about a listing
     */
    public function requestInfo(Request $request, Listing $listing): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create listing request
        \App\Models\ListingRequest::create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        // Notify listing owner
        // Implement notification here

        return response()->json(['message' => 'Request sent successfully']);
    }

    /**
     * Report a listing
     */
    public function report(Request $request, Listing $listing): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create report (implement Report model if needed)
        // For now, we can log or create a moderation task

        return response()->json(['message' => 'Report submitted successfully']);
    }
}
