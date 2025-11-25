<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgentService;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /**
     * Display agents for authenticated users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'agent');

        if ($request->has('location')) {
            $query->where('country', 'like', '%' . $request->location . '%');
        }

        if ($request->has('service_type')) {
            $query->whereHas('agentServices', function ($q) use ($request) {
                $q->where('service_type', $request->service_type);
            });
        }

        $agents = $query->select('id', 'name', 'email', 'country', 'phone')
            ->with('agentServices')
            ->paginate(20);

        return response()->json($agents);
    }

    /**
     * Display agents for public access
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $query = User::where('role', 'agent')->where('is_active', true);

        if ($request->has('location')) {
            $query->where('country', 'like', '%' . $request->location . '%');
        }

        $agents = $query->select('id', 'name', 'country')
            ->paginate(20);

        return response()->json($agents);
    }

    /**
     * Display the specified agent
     */
    public function show(User $agent): JsonResponse
    {
        if ($agent->role !== 'agent') {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        $agent->load(['agentServices', 'reviews' => function ($query) {
            $query->latest()->limit(5);
        }]);

        return response()->json($agent);
    }

    /**
     * Request service from an agent
     */
    public function requestService(Request $request, User $agent): JsonResponse
    {
        if ($agent->role !== 'agent') {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'service_type' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'budget' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service = AgentService::create([
            'user_id' => $request->user()->id,
            'agent_id' => $agent->id,
            'service_type' => $request->service_type,
            'description' => $request->description,
            'budget' => $request->budget,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Service request sent successfully',
            'service' => $service
        ], 201);
    }

    /**
     * Get agent reviews
     */
    public function reviews(User $agent): JsonResponse
    {
        if ($agent->role !== 'agent') {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        $reviews = $agent->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($reviews);
    }

    /**
     * Submit review for an agent
     */
    public function submitReview(Request $request, User $agent): JsonResponse
    {
        if ($agent->role !== 'agent') {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'agent_id' => $agent->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review
        ], 201);
    }

    /**
     * Agent dashboard stats
     */
    public function dashboardStats(Request $request): JsonResponse
    {
        $agent = $request->user();

        $stats = [
            'total_services' => AgentService::where('agent_id', $agent->id)->count(),
            'pending_services' => AgentService::where('agent_id', $agent->id)->where('status', 'pending')->count(),
            'completed_services' => AgentService::where('agent_id', $agent->id)->where('status', 'completed')->count(),
            'average_rating' => $agent->reviews()->avg('rating') ?? 0,
            'total_reviews' => $agent->reviews()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get agent's services
     */
    public function myServices(Request $request): JsonResponse
    {
        $services = AgentService::where('agent_id', $request->user()->id)
            ->with('user:id,name,email')
            ->latest()
            ->paginate(20);

        return response()->json($services);
    }

    /**
     * Accept a service request
     */
    public function acceptService(Request $request, AgentService $service): JsonResponse
    {
        if ($service->agent_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($service->status !== 'pending') {
            return response()->json(['message' => 'Service cannot be accepted'], 400);
        }

        $service->update(['status' => 'accepted']);

        return response()->json([
            'message' => 'Service accepted successfully',
            'service' => $service
        ]);
    }

    /**
     * Complete a service
     */
    public function completeService(Request $request, AgentService $service): JsonResponse
    {
        if ($service->agent_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($service->status !== 'accepted') {
            return response()->json(['message' => 'Service cannot be completed'], 400);
        }

        $service->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Service completed successfully',
            'service' => $service
        ]);
    }
}