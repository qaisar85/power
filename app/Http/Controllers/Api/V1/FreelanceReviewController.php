<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\FreelanceService;
use App\Models\FreelanceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FreelanceReviewController extends Controller
{
    public function index(FreelanceService $service)
    {
        $reviews = Review::where('service_id', $service->id)
            ->where('status', 'visible')
            ->orderByDesc('id')
            ->paginate(20);
        return response()->json($reviews);
    }

    public function store(Request $request, FreelanceService $service)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $hasOrder = FreelanceOrder::where('service_id', $service->id)
            ->where('buyer_id', $user->id)
            ->whereIn('status', ['paid','completed'])
            ->exists();

        if (! $hasOrder) {
            return response()->json(['error' => 'You must purchase before reviewing'], 422);
        }

        $data = $request->validate([
            'stars' => ['required','integer','min:1','max:5'],
            'message' => ['nullable','string','max:2000'],
            'recaptcha_token' => ['nullable','string'],
        ]);

        $review = Review::create([
            'company_id' => null,
            'user_id' => $user->id,
            'stars' => $data['stars'],
            'message' => $data['message'] ?? null,
            'status' => 'visible',
            'service_id' => $service->id,
        ]);

        return response()->json($review, 201);
    }

    public function rating(FreelanceService $service)
    {
        $avg = Review::where('service_id', $service->id)
            ->where('status', 'visible')
            ->avg('stars');
        return response()->json(['rating' => $avg ? round($avg, 1) : null]);
    }
}
