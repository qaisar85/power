<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::active()
            ->orderBy('priority')
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    /**
     * Get categories by priority
     */
    public function byPriority(int $priority): JsonResponse
    {
        $categories = Category::active()
            ->where('priority', $priority)
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    /**
     * Get priority 1 categories (Green)
     */
    public function priority1(): JsonResponse
    {
        $categories = Category::active()
            ->priority1()
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    /**
     * Get priority 2 categories (Yellow)
     */
    public function priority2(): JsonResponse
    {
        $categories = Category::active()
            ->priority2()
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    /**
     * Get categories grouped by priority
     */
    public function grouped(): JsonResponse
    {
        $priority1 = Category::active()->priority1()->orderBy('sort_order')->get();
        $priority2 = Category::active()->priority2()->orderBy('sort_order')->get();

        return response()->json([
            'priority_1' => [
                'name' => 'Green Priority',
                'color' => '#28A745',
                'categories' => $priority1
            ],
            'priority_2' => [
                'name' => 'Yellow Priority',
                'color' => '#FFC107',
                'categories' => $priority2
            ]
        ]);
    }
}