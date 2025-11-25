<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FreelanceSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminFreelanceCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('type', 'freelance')->orderBy('sort_order')->get();
        $subcategories = FreelanceSubcategory::orderBy('sort_order')->get();
        return Inertia::render('Admin/Freelance/Categories', [
            'categories' => $categories,
            'subcategories' => $subcategories,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'sort_order' => ['nullable','integer'],
            'is_active' => ['nullable','boolean'],
        ]);
        $slug = Str::slug($data['name']);
        Category::updateOrCreate(
            ['slug' => $slug],
            array_merge($data, ['slug' => $slug, 'type' => 'freelance'])
        );
        return back()->with('success', 'Category saved');
    }

    public function storeSubcategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'category_slug' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'sort_order' => ['nullable','integer'],
            'is_active' => ['nullable','boolean'],
        ]);
        $slug = Str::slug($data['name']);
        FreelanceSubcategory::updateOrCreate(
            ['slug' => $slug],
            array_merge($data, ['slug' => $slug])
        );
        return back()->with('success', 'Subcategory saved');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted');
    }

    public function destroySubcategory(FreelanceSubcategory $subcategory)
    {
        $subcategory->delete();
        return back()->with('success', 'Subcategory deleted');
    }
}

