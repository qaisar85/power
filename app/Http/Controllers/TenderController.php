<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TenderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->company ?? null;
        $needsCompany = !$company;

        $tenders = Tender::query()
            ->where(function ($q) use ($user, $company) {
                $q->where('user_id', $user->id);
                if ($company) {
                    $q->orWhere('company_id', $company->id);
                }
            })
            ->withCount('applications')
            ->latest()
            ->paginate(20);

        return Inertia::render('ServiceDashboard/Tenders/Index', [
            'tenders' => $tenders,
            'needs_company' => $needsCompany,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $company = $user->company ?? null;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'subcategory' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'country' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'deadline_at' => ['nullable', 'date'],
            'visibility' => ['required', 'in:public,link,private'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:5120'],
        ]);

        $tender = new Tender($validated);
        $tender->user_id = $user->id;
        if ($company) {
            $tender->company_id = $company->id;
        }
        if ($validated['visibility'] === 'link') {
            $tender->link_token = bin2hex(random_bytes(16));
        }
        $tender->status = 'pending';
        $tender->attachments = [];
        $tender->save();

        if ($request->hasFile('attachments')) {
            $stored = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("tenders/{$tender->id}", 'public');
                $stored[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
            $tender->attachments = array_merge($tender->attachments ?? [], $stored);
            $tender->save();
        }

        return redirect()->route('service.tenders.index')->with('success', 'Tender submitted for moderation.');
    }

    public function update(Request $request, Tender $tender)
    {
        $user = $request->user();
        $company = $user->company ?? null;
        if ($tender->user_id !== $user->id && (!$company || $tender->company_id !== $company->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'subcategory' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string'],
            'country' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'deadline_at' => ['nullable', 'date'],
            'visibility' => ['required', 'in:public,link,private'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:5120'],
        ]);

        $tender->fill($validated);
        if ($validated['visibility'] === 'link' && !$tender->link_token) {
            $tender->link_token = bin2hex(random_bytes(16));
        }
        $tender->save();

        if ($request->hasFile('attachments')) {
            $stored = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("tenders/{$tender->id}", 'public');
                $stored[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
            $tender->attachments = array_merge($tender->attachments ?? [], $stored);
            $tender->save();
        }

        return back()->with('success', 'Tender updated.');
    }

    public function destroy(Request $request, Tender $tender)
    {
        $user = $request->user();
        $company = $user->company ?? null;
        if ($tender->user_id !== $user->id && (!$company || $tender->company_id !== $company->id)) {
            abort(403);
        }

        // delete attachment files
        foreach (($tender->attachments ?? []) as $file) {
            if (!empty($file['path'])) {
                Storage::disk('public')->delete($file['path']);
            }
        }

        $tender->delete();
        return back()->with('success', 'Tender deleted.');
    }

    public function extend(Request $request, Tender $tender)
    {
        $user = $request->user();
        $company = $user->company ?? null;
        if ($tender->user_id !== $user->id && (!$company || $tender->company_id !== $company->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'extend_days' => ['required', 'integer', 'min:1', 'max:180'],
        ]);

        if ($tender->deadline_at) {
            $tender->deadline_at = $tender->deadline_at->addDays($validated['extend_days']);
        } else {
            $tender->deadline_at = now()->addDays($validated['extend_days']);
        }
        $tender->save();

        return back()->with('success', 'Tender deadline extended.');
    }
}