<?php

namespace App\Http\Controllers;

use App\Models\DrillingCompany;
use App\Models\HseDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DrillingHseDocumentController extends Controller
{
    public function index(Request $request)
    {
        $company = DrillingCompany::where('user_id', $request->user()->id)->firstOrFail();
        $docs = HseDocument::where('company_id', $company->id)
            ->orderBy('expires_at', 'asc')
            ->get(['id', 'title', 'type', 'file', 'issued_at', 'expires_at', 'verified']);

        return Inertia::render('ServiceDashboard/Drilling/HSE/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'documents' => $docs,
        ]);
    }

    public function store(Request $request)
    {
        $company = DrillingCompany::where('user_id', $request->user()->id)->firstOrFail();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'region' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $doc = new HseDocument();
        $doc->company_id = $company->id;
        $doc->title = $data['title'];
        $doc->type = $data['type'];
        $doc->issued_at = $data['issued_at'] ?? null;
        $doc->expires_at = $data['expires_at'] ?? null;
        $doc->region = $data['region'] ?? null;
        $doc->description = $data['description'] ?? null;
        $doc->verified = false;
        $doc->file = '';
        $doc->save();

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('drilling/hse/' . $doc->id, 'public');
            $doc->file = $path;
            $doc->save();
        }

        return redirect()->route('service.drilling.hse.index')->with('success', 'Document added');
    }

    public function verify(Request $request, HseDocument $document)
    {
        $company = DrillingCompany::where('user_id', $request->user()->id)->firstOrFail();
        if ($document->company_id !== $company->id) {
            abort(403);
        }
        $document->verified = true;
        $document->save();
        return redirect()->route('service.drilling.hse.index')->with('success', 'Document verified');
    }

    public function destroy(Request $request, HseDocument $document)
    {
        $company = DrillingCompany::where('user_id', $request->user()->id)->firstOrFail();
        if ($document->company_id !== $company->id) {
            abort(403);
        }
        Storage::disk('public')->delete($document->file);
        Storage::disk('public')->deleteDirectory('drilling/hse/' . $document->id);
        $document->delete();
        return redirect()->route('service.drilling.hse.index')->with('success', 'Document removed');
    }
}