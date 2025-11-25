<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use App\Models\TenderApplication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TenderController extends Controller
{
    /**
     * Display available tenders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tender::where('status', 'published');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('budget_min')) {
            $query->where('budget', '>=', $request->budget_min);
        }

        if ($request->has('budget_max')) {
            $query->where('budget', '<=', $request->budget_max);
        }

        $tenders = $query->with('user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($tenders);
    }

    /**
     * Display the specified tender
     */
    public function show(Tender $tender): JsonResponse
    {
        if ($tender->status !== 'published') {
            return response()->json(['message' => 'Tender not found'], 404);
        }

        $tender->load('user:id,name,email');

        return response()->json($tender);
    }

    /**
     * Store a newly created tender
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:100',
            'budget' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'location' => 'nullable|string|max:255',
            'deadline' => 'nullable|date|after:today',
            'requirements' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tender = $request->user()->tenders()->create(array_merge(
            $validator->validated(),
            ['status' => 'draft']
        ));

        return response()->json($tender, 201);
    }

    /**
     * Apply to a tender
     */
    public function apply(Request $request, Tender $tender): JsonResponse
    {
        if ($tender->status !== 'published') {
            return response()->json(['message' => 'Tender is not available'], 400);
        }

        if ($tender->user_id === $request->user()->id) {
            return response()->json(['message' => 'Cannot apply to your own tender'], 400);
        }

        $existingApplication = TenderApplication::where('tender_id', $tender->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingApplication) {
            return response()->json(['message' => 'You have already applied to this tender'], 400);
        }

        $validator = Validator::make($request->all(), [
            'proposal' => 'required|string|max:2000',
            'budget' => 'nullable|numeric|min:0',
            'timeline' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $application = TenderApplication::create([
            'tender_id' => $tender->id,
            'user_id' => $request->user()->id,
            'proposal' => $request->proposal,
            'budget' => $request->budget,
            'timeline' => $request->timeline,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Application submitted successfully',
            'application' => $application
        ], 201);
    }

    /**
     * Get user's tender applications
     */
    public function myApplications(Request $request): JsonResponse
    {
        $applications = TenderApplication::where('user_id', $request->user()->id)
            ->with('tender:id,title,budget,currency,deadline')
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }
}