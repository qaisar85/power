<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Review;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ReviewPublicController extends Controller
{
    public function store(Request $request, Company $company)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $data = $request->validate([
            'stars' => ['required','integer','min:1','max:5'],
            'message' => ['nullable','string'],
        ]);

        $review = Review::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'stars' => $data['stars'],
            'message' => $data['message'] ?? null,
            'status' => 'pending',
        ]);

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $user->id,
            'action' => 'review_submitted',
            'context' => [ 'review_id' => $review->id, 'stars' => $review->stars ],
        ]);

        return back()->with('success', 'Review submitted and pending moderation.');
    }
}