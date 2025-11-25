<?php

namespace App\Http\Controllers;

use App\Models\Tender;
use App\Models\TenderApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenderApplicationController extends Controller
{
    public function store(Request $request, Tender $tender)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }
        if (!$tender->isOpen()) {
            return back()->withErrors(['deadline' => 'Tender is closed.']);
        }

        $validated = $request->validate([
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'deadline_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'comment' => ['nullable', 'string'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:5120'],
        ]);

        $application = new TenderApplication($validated);
        $application->tender_id = $tender->id;
        $application->user_id = $user->id;
        $application->status = 'submitted';
        $application->files = [];
        $application->save();

        if ($request->hasFile('files')) {
            $stored = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store("tenders/{$tender->id}/applications/{$application->id}", 'public');
                $stored[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
            $application->files = array_merge($application->files ?? [], $stored);
            $application->save();
        }

        return back()->with('success', 'Application submitted.');
    }
}