<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Search listings
     */
    public function listings(Request $request): JsonResponse
    {
        $query = Listing::where('status', 'published');

        if ($request->has('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $listings = $query->with('user:id,name')->paginate(20);

        return response()->json($listings);
    }

    /**
     * Search companies
     */
    public function companies(Request $request): JsonResponse
    {
        $query = Company::query();

        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $companies = $query->paginate(20);

        return response()->json($companies);
    }

    /**
     * Search agents
     */
    public function agents(Request $request): JsonResponse
    {
        $query = User::where('role', 'agent');

        if ($request->has('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->has('location')) {
            $query->where('country', 'like', '%' . $request->location . '%');
        }

        $agents = $query->select('id', 'name', 'email', 'country')->paginate(20);

        return response()->json($agents);
    }

    /**
     * Get available filters
     */
    public function availableFilters(): JsonResponse
    {
        return response()->json([
            'categories' => $this->getCategories(),
            'countries' => $this->getCountries(),
            'currencies' => ['USD', 'EUR', 'GBP', 'AED'],
            'deal_types' => ['sale', 'rent', 'auction'],
            'listing_types' => ['product', 'service', 'vacancy', 'news', 'tender', 'auction']
        ]);
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Listing::where('status', 'published')
            ->where('title', 'like', '%' . $query . '%')
            ->select('title')
            ->distinct()
            ->limit(10)
            ->pluck('title');

        return response()->json($suggestions);
    }

    /**
     * Get categories (public endpoint)
     */
    public function categories(): JsonResponse
    {
        return response()->json($this->getCategories());
    }

    /**
     * Get countries (public endpoint)
     */
    public function countries(): JsonResponse
    {
        return response()->json($this->getCountries());
    }

    private function getCategories(): array
    {
        return [
            'Electronics',
            'Fashion',
            'Home & Garden',
            'Automotive',
            'Real Estate',
            'Services',
            'Jobs',
            'Other'
        ];
    }

    private function getCountries(): array
    {
        return [
            'United States',
            'United Kingdom',
            'Canada',
            'Australia',
            'Germany',
            'France',
            'UAE',
            'Saudi Arabia',
            'Other'
        ];
    }
}