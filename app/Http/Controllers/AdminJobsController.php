<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminJobsController extends Controller
{
    public function index(Request $request)
    {
        $jobs = Job::orderByDesc('id')->paginate(30);
        return Inertia::render('Admin/Jobs/Index', [
            'jobs' => $jobs,
        ]);
    }

    public function approve(Request $request, Job $job)
    {
        $job->status = 'published';
        $job->save();
        return back()->with('success', 'Job approved');
    }

    public function reject(Request $request, Job $job)
    {
        $job->status = 'rejected';
        $job->save();
        return back()->with('success', 'Job rejected');
    }
}

