<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\ServiceCase;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $company = Company::where('user_id', $user->id)->first();

        if (!$company) {
            return Inertia::render('ServiceDashboard/Index', [
                'company' => null,
                'metrics' => [
                    'active_services' => 0,
                    'new_requests' => 0,
                    'completed_cases' => 0,
                    'rating' => null,
                ],
                'subscription' => [
                    'plan' => null,
                    'expires_at' => null,
                    'is_verified' => false,
                ],
                'can_publish' => false,
                'needs_company' => true,
            ]);
        }

        $activeServices = Service::where('company_id', $company->id)
            ->where('visibility', 'published')
            ->count();
        $newRequests = ServiceRequest::whereHas('service', function($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->where('status', 'new')
            ->count();
        $completedCases = ServiceCase::whereHas('service', function($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->count();
        $avgRating = Review::where('company_id', $company->id)->where('status', 'visible')->avg('stars');

        return Inertia::render('ServiceDashboard/Index', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'is_verified' => (bool) $company->is_verified,
                'plan' => optional($company->plan)->name,
                'plan_expires_at' => $company->plan_expires_at,
            ],
            'metrics' => [
                'active_services' => $activeServices,
                'new_requests' => $newRequests,
                'completed_cases' => $completedCases,
                'rating' => $avgRating ? round($avgRating, 1) : null,
            ],
            'subscription' => [
                'plan' => optional($company->plan)->name,
                'expires_at' => $company->plan_expires_at,
                'is_verified' => (bool) $company->is_verified,
            ],
            'can_publish' => (bool) $company->is_verified,
            'needs_company' => false,
        ]);
    }
}