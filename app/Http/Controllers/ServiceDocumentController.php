<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ServiceDocument;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ServiceDocumentController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $documents = ServiceDocument::where('company_id', $company->id)->orderByDesc('id')->paginate(20);
        return Inertia::render('ServiceDashboard/Documents/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'type' => 'required|string|max:100',
            'file' => 'required|file|max:10240',
            'expires_at' => 'nullable|date',
        ]);

        $path = $request->file('file')->store("service_documents/{$company->id}", 'public');

        $document = ServiceDocument::create([
            'company_id' => $company->id,
            'type' => $data['type'],
            'filename' => $path,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => 'pending',
        ]);

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'document_uploaded',
            'context' => [ 'document_id' => $document->id, 'type' => $document->type ],
        ]);

        return back()->with('success', 'Document uploaded and pending verification.');
    }

    public function verify(Request $request, ServiceDocument $document)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($document->company_id === $company->id, 403);
        $document->update(['status' => 'verified', 'is_rejected' => false]);

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'document_verified',
            'context' => [ 'document_id' => $document->id, 'type' => $document->type ],
        ]);

        return back()->with('success', 'Document verified.');
    }

    public function reject(Request $request, ServiceDocument $document)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($document->company_id === $company->id, 403);
        $document->update(['is_rejected' => true]);

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'document_rejected',
            'context' => [ 'document_id' => $document->id, 'type' => $document->type ],
        ]);

        return back()->with('success', 'Document rejected.');
    }
}