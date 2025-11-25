<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Review;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceReviewController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $reviews = Review::where('company_id', $company->id)
            ->orderByDesc('id')
            ->paginate(20);

        $rating = Review::where('company_id', $company->id)->where('status', 'visible')->avg('stars');

        return Inertia::render('ServiceDashboard/Reviews/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'reviews' => $reviews,
            'rating' => $rating ? round($rating, 1) : null,
        ]);
    }

    public function report(Request $request, Review $review)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($review->company_id === $company->id, 403);
        $review->update(['status' => 'reported']);

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'review_reported',
            'context' => [ 'review_id' => $review->id, 'stars' => $review->stars ],
        ]);

        return back()->with('success', 'Review has been reported.');
    }

    public function reply(Request $request, Review $review)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($review->company_id === $company->id, 403);

        $data = $request->validate([
            'message' => ['required','string','max:2000'],
        ]);

        $review->update([
            'reply_message' => $data['message'],
            'reply_user_id' => $request->user()->id,
            'reply_at' => now(),
        ]);

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'review_replied',
            'context' => [ 'review_id' => $review->id ],
        ]);

        return back()->with('success', 'Reply added to review.');
    }
}