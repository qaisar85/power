<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BusinessSector;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusinessSectorController extends Controller
{
    /**
     * Get all sectors (level 1)
     */
    public function sectors(): JsonResponse
    {
        $sectors = BusinessSector::sectors()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($sectors);
    }

    /**
     * Get sub-sectors for a sector
     */
    public function subSectors(BusinessSector $sector): JsonResponse
    {
        $subSectors = $sector->children()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($subSectors);
    }

    /**
     * Get sub-sub-sectors for a sub-sector
     */
    public function subSubSectors(BusinessSector $subSector): JsonResponse
    {
        $subSubSectors = $subSector->children()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($subSubSectors);
    }

    /**
     * Get complete sector hierarchy
     */
    public function hierarchy(): JsonResponse
    {
        $sectors = BusinessSector::sectors()
            ->with(['children.children'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($sectors);
    }

    /**
     * Search sectors by name
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $sectors = BusinessSector::where('name', 'like', '%' . $query . '%')
            ->where('is_active', true)
            ->with('parent')
            ->limit(20)
            ->get();

        return response()->json($sectors);
    }
}