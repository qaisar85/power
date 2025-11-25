<?php

namespace App\Http\Controllers;

use App\Models\DrillingCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DrillingCaseController extends Controller
{
    public function index(Request $request)
    {
        // Safely resolve user/company to avoid null access
        $user = $request->user();
        $company = $user ? $user->company : null;
    
        // Parse tag filters from query
        $tagsInput = $request->input('tags');
        $tags = null;
        if (is_array($tagsInput)) {
            $tags = array_values(array_filter(array_map('trim', $tagsInput), fn($t) => strlen($t) > 0));
        } elseif (is_string($tagsInput)) {
            $tags = array_values(array_filter(array_map('trim', explode(',', $tagsInput)), fn($t) => strlen($t) > 0));
        }
    
        // If no company, render page with empty datasets
        if (!$company) {
            return Inertia::render('ServiceDashboard/Drilling/Cases/Index', [
                'company' => null,
                'cases' => [],
                'rigs' => [],
                'filters' => [ 'tags' => $tags ],
            ]);
        }
    
        $cases = DrillingCase::where('company_id', $company->id)
            ->when($tags, function ($q) use ($tags) {
                foreach ($tags as $t) {
                    $q->whereJsonContains('tags', $t);
                }
            })
            ->orderByDesc('start_date')
            ->orderBy('title')
            ->get([
                'id','company_id','rig_id','title','client','region','method','depth','start_date','end_date','status','summary','tags','photos','documents','metrics','verified'
            ]);

        $rigs = \App\Models\DrillingRig::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id','name']);

        return Inertia::render('ServiceDashboard/Drilling/Cases/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'cases' => $cases,
            'rigs' => $rigs,
            'filters' => [ 'tags' => $tags ],
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->company;
        if (!$company) { abort(403); }

        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'client' => ['nullable','string','max:255'],
            'region' => ['nullable','string','max:255'],
            'method' => ['nullable','string','max:255'],
            'depth' => ['nullable','integer','min:0'],
            'rig_id' => ['nullable','integer'],
            'start_date' => ['nullable','date'],
            'end_date' => ['nullable','date','after_or_equal:start_date'],
            'status' => ['nullable','in:planned,in_progress,completed,cancelled'],
            'summary' => ['nullable','string'],
            'tags' => ['nullable'],
            'photos.*' => ['nullable','file','mimes:jpg,jpeg,png,webp'],
            'documents.*' => ['nullable','file','mimes:pdf'],
        ]);

        $case = new DrillingCase();
        $case->company_id = $company->id;
        $case->rig_id = $data['rig_id'] ?? null;
        $case->title = $data['title'];
        $case->client = $data['client'] ?? null;
        $case->region = $data['region'] ?? null;
        $case->method = $data['method'] ?? null;
        $case->depth = $data['depth'] ?? null;
        $case->start_date = $data['start_date'] ?? null;
        $case->end_date = $data['end_date'] ?? null;
        $case->status = $data['status'] ?? null;
        $case->summary = $data['summary'] ?? null;

        // Parse tags from array or comma string
        $tagsInput = $request->input('tags');
        if (is_array($tagsInput)) {
            $case->tags = array_values(array_filter(array_map('trim', $tagsInput), fn($t) => strlen($t) > 0));
        } elseif (is_string($tagsInput)) {
            $case->tags = array_values(array_filter(array_map('trim', explode(',', $tagsInput)), fn($t) => strlen($t) > 0));
        }

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $photos[] = $file->store("drilling/cases/{$company->id}/photos", 'public');
            }
        }
        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $documents[] = $file->store("drilling/cases/{$company->id}/documents", 'public');
            }
        }

        $case->photos = $photos;
        $case->documents = $documents;
        $case->metrics = [];
        $case->save();

        return redirect()->back()->with('success', 'Case created');
    }

    public function update(Request $request, DrillingCase $case)
    {
        $company = $request->user()->company;
        if (!$company) { abort(403); }
        if ($case->company_id !== $company->id) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['nullable','string','max:255'],
            'client' => ['nullable','string','max:255'],
            'region' => ['nullable','string','max:255'],
            'method' => ['nullable','string','max:255'],
            'depth' => ['nullable','integer','min:0'],
            'rig_id' => ['nullable','integer'],
            'start_date' => ['nullable','date'],
            'end_date' => ['nullable','date','after_or_equal:start_date'],
            'status' => ['nullable','in:planned,in_progress,completed,cancelled'],
            'summary' => ['nullable','string'],
            'tags' => ['nullable'],
            'verified' => ['nullable','boolean'],
            'photos.*' => ['nullable','file','mimes:jpg,jpeg,png,webp'],
            'documents.*' => ['nullable','file','mimes:pdf'],
        ]);

        // Update scalar fields if provided
        foreach (['title','client','region','method','depth','rig_id','start_date','end_date','status','summary','verified'] as $field) {
            if (array_key_exists($field, $data)) {
                $case->{$field} = $data[$field] ?? null;
            }
        }

        // Tags
        $tagsInput = $request->input('tags');
        if (!is_null($tagsInput)) {
            if (is_array($tagsInput)) {
                $case->tags = array_values(array_filter(array_map('trim', $tagsInput), fn($t) => strlen($t) > 0));
            } elseif (is_string($tagsInput)) {
                $case->tags = array_values(array_filter(array_map('trim', explode(',', $tagsInput)), fn($t) => strlen($t) > 0));
            }
        }

        // Optional: append new files if provided
        $photos = $case->photos ?: [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $photos[] = $file->store("drilling/cases/{$company->id}/photos", 'public');
            }
        }
        $documents = $case->documents ?: [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $documents[] = $file->store("drilling/cases/{$company->id}/documents", 'public');
            }
        }
        $case->photos = $photos;
        $case->documents = $documents;

        $case->save();
        return redirect()->back()->with('success', 'Case updated');
    }

    public function destroy(Request $request, DrillingCase $case)
    {
        $company = $request->user()->company;
        if (!$company) { abort(403); }
        if ($case->company_id !== $company->id) {
            abort(403);
        }

        foreach (($case->photos ?? []) as $p) {
            if ($p && Storage::disk('public')->exists($p)) {
                Storage::disk('public')->delete($p);
            }
        }
        foreach (($case->documents ?? []) as $d) {
            if ($d && Storage::disk('public')->exists($d)) {
                Storage::disk('public')->delete($d);
            }
        }

        $case->delete();
        return redirect()->back()->with('success', 'Case removed');
    }
}