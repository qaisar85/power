<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Get all countries
     */
    public function countries(): JsonResponse
    {
        $countries = Location::countries()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'currency', 'timezone']);

        return response()->json($countries);
    }

    /**
     * Get states for a country
     */
    public function states(Location $country): JsonResponse
    {
        if ($country->type !== 'country') {
            return response()->json(['message' => 'Invalid country'], 400);
        }

        $states = $country->children()
            ->where('type', 'state')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($states);
    }

    /**
     * Get cities for a state
     */
    public function cities(Location $state): JsonResponse
    {
        if ($state->type !== 'state') {
            return response()->json(['message' => 'Invalid state'], 400);
        }

        $cities = $state->children()
            ->where('type', 'city')
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude']);

        return response()->json($cities);
    }

    /**
     * Get districts for a city
     */
    public function districts(Location $city): JsonResponse
    {
        if ($city->type !== 'city') {
            return response()->json(['message' => 'Invalid city'], 400);
        }

        $districts = $city->children()
            ->where('type', 'district')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($districts);
    }

    /**
     * Get villages for a district
     */
    public function villages(Location $district): JsonResponse
    {
        if ($district->type !== 'district') {
            return response()->json(['message' => 'Invalid district'], 400);
        }

        $villages = $district->children()
            ->where('type', 'village')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($villages);
    }

    /**
     * Search locations
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $locations = Location::where('name', 'like', '%' . $query . '%')
            ->where('is_active', true)
            ->when($type, function ($q) use ($type) {
                return $q->where('type', $type);
            })
            ->with('parent:id,name,type')
            ->limit(20)
            ->get(['id', 'name', 'type', 'parent_id']);

        return response()->json($locations);
    }
}