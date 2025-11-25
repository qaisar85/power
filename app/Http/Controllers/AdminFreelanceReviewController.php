<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFreelanceReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::whereNotNull('service_id')
            ->orderByDesc('id')
            ->paginate(30);
        return Inertia::render('Admin/Freelance/Reviews', [
            'reviews' => $reviews,
        ]);
    }

    public function setStatus(Request $request, Review $review)
    {
        $data = $request->validate([
            'status' => ['required','string','in:visible,hidden,reported'],
        ]);
        $review->status = $data['status'];
        $review->save();
        return back()->with('success', 'Review status updated');
    }
}

