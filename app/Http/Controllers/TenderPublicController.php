<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenderPublicController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'country' => $request->string('country')->toString(),
            'category' => $request->string('category')->toString(),
            'type' => $request->string('type')->toString(), // Purchase / Rent / Service / Repair
            'deadline' => $request->string('deadline')->toString(),
            'budget_min' => $request->input('budget_min'),
            'budget_max' => $request->input('budget_max'),
            'sort' => $request->string('sort')->toString(),
        ];

        $query = Tender::query()->where('visibility', 'public')->where('status', 'published');

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['deadline'])) {
            $query->whereNotNull('deadline_at')->where('deadline_at', '<=', $filters['deadline']);
        }
        if (!empty($filters['budget_min'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('budget_min', '>=', $filters['budget_min'])
                  ->orWhere('budget_max', '>=', $filters['budget_min']);
            });
        }
        if (!empty($filters['budget_max'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('budget_max', '<=', $filters['budget_max'])
                  ->orWhere('budget_min', '<=', $filters['budget_max']);
            });
        }

        if ($filters['sort'] === 'budget') {
            $query->orderBy('budget_max', 'desc');
        } elseif ($filters['sort'] === 'deadline') {
            $query->orderBy('deadline_at', 'asc');
        } else {
            $query->latest();
        }

        $tenders = $query->paginate(12)->withQueryString();

        return Inertia::render('Tenders/Index', [
            'tenders' => $tenders,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Tender $tender)
    {
        if ($tender->visibility === 'private' || ($tender->visibility === 'link' && $tender->link_token && $request->string('token')->toString() !== $tender->link_token)) {
            abort(404);
        }

        $canApply = auth()->check() && $tender->isOpen() && auth()->id() !== $tender->user_id;

        return Inertia::render('Tenders/Show', [
            'tender' => $tender->loadCount('applications'),
            'canApply' => $canApply,
            'attachments' => $tender->attachments ?? [],
        ]);
    }
}