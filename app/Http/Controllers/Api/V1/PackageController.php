<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    /**
     * Display available packages
     */
    public function index(): JsonResponse
    {
        $packages = Package::where('is_active', true)
            ->orderBy('price')
            ->get();

        return response()->json($packages);
    }

    /**
     * Display the specified package
     */
    public function show(Package $package): JsonResponse
    {
        if (!$package->is_active) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        return response()->json($package);
    }

    /**
     * Subscribe to a package
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package = Package::findOrFail($request->package_id);
        
        if (!$package->is_active) {
            return response()->json(['message' => 'Package is not available'], 400);
        }

        // Check if user already has active subscription
        $activeSubscription = $request->user()->subscriptions()
            ->where('status', 'active')
            ->first();

        if ($activeSubscription) {
            return response()->json(['message' => 'You already have an active subscription'], 400);
        }

        // Create subscription
        $subscription = $request->user()->subscriptions()->create([
            'package_id' => $package->id,
            'status' => 'pending',
            'amount' => $package->price,
            'currency' => $package->currency,
            'starts_at' => now(),
            'expires_at' => now()->addDays($package->duration_days),
        ]);

        return response()->json([
            'message' => 'Subscription created successfully',
            'subscription' => $subscription->load('package')
        ], 201);
    }

    /**
     * Get user's subscriptions
     */
    public function mySubscriptions(Request $request): JsonResponse
    {
        $subscriptions = $request->user()->subscriptions()
            ->with('package')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($subscriptions);
    }

    /**
     * Cancel a subscription
     */
    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {
        if ($subscription->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($subscription->status === 'cancelled') {
            return response()->json(['message' => 'Subscription is already cancelled'], 400);
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'subscription' => $subscription
        ]);
    }
}