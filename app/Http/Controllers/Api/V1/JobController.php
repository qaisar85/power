<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\JobApplicationRequest;

class JobController extends Controller
{
    /**
     * Display available jobs
     */
    public function index(Request $request): JsonResponse
    {
        $query = Job::where('status', 'published');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        if ($request->has('salary_min')) {
            $query->where('salary_min', '>=', $request->salary_min);
        }

        $jobs = $query->with(['user:id,name', 'company:id,name'])
            ->latest()
            ->paginate(20);

        return response()->json($jobs);
    }

    /**
     * Display the specified job
     */
    public function show(Job $job): JsonResponse
    {
        if ($job->status !== 'published') {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $job->load(['user:id,name,email', 'company']);

        return response()->json($job);
    }

    /**
     * Store a newly created job
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company_id' => 'nullable|exists:companies,id',
            'category' => 'nullable|string|max:100',
            'job_type' => 'nullable|in:full-time,part-time,contract,freelance',
            'location' => 'nullable|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $job = $request->user()->jobs()->create(array_merge(
            $validator->validated(),
            ['status' => 'draft']
        ));

        return response()->json($job, 201);
    }

    /**
     * Apply to a job
     */
    public function apply(JobApplicationRequest $request, Job $job): JsonResponse
    {
        if ($job->status !== 'published') {
            return response()->json(['message' => 'Job is not available'], 400);
        }

        if ($job->user_id === $request->user()->id) {
            return response()->json(['message' => 'Cannot apply to your own job'], 400);
        }

        $existingApplication = JobApplication::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingApplication) {
            return response()->json(['message' => 'You have already applied to this job'], 400);
        }

        $applicationData = $request->validated();
        $applicationData['recaptcha_token'] = $request->input('recaptcha_token');
        $applicationData['job_id'] = $job->id;
        $applicationData['user_id'] = $request->user()->id;
        $applicationData['status'] = 'pending';
        $applicationData['resume'] = $applicationData['resume_file'] ?? null;
        unset($applicationData['resume_file']);

        $application = JobApplication::create($applicationData);

        return response()->json([
            'message' => 'Job application submitted successfully',
            'application' => $application->load('job:id,title,company_id')
        ], 201);
    }

    /**
     * Get user's job applications
     */
    public function myApplications(Request $request): JsonResponse
    {
        $applications = JobApplication::where('user_id', $request->user()->id)
            ->with('job:id,title,salary_min,salary_max,currency,location')
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }
}
