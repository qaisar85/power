<?php

namespace App\Http\Controllers;

use App\Models\FreelanceProject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FreelanceProjectController extends Controller
{
    public function create()
    {
        return Inertia::render('Freelance/Projects/Create', [
            'defaults' => [
                'currency' => 'USD',
                'budget_type' => 'fixed',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'budget_type' => ['required', 'string'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'location' => ['nullable', 'string', 'max:255'],
            'deadline_at' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array'],
        ]);

        $slugBase = Str::slug($data['title']);
        $slug = $slugBase . '-' . Str::lower(Str::random(6));

        $project = FreelanceProject::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'budget_type' => $data['budget_type'],
            'budget_min' => $data['budget_min'] ?? null,
            'budget_max' => $data['budget_max'] ?? null,
            'currency' => $data['currency'],
            'location' => $data['location'] ?? null,
            'deadline_at' => $data['deadline_at'] ?? null,
            'status' => 'pending',
            'attachments' => $data['attachments'] ?? [],
        ]);

        return redirect()->route('freelance.projects.show', $project->slug)
            ->with('status', 'Project submitted for moderation.');
    }

    public function show(FreelanceProject $project)
    {
        return Inertia::render('Freelance/Projects/Show', [
            'project' => $project,
        ]);
    }
}