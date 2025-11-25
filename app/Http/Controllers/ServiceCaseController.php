<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Service;
use App\Models\ServiceCase;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ServiceCaseController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $cases = ServiceCase::with('service')
            ->whereHas('service', function($q) use ($company) { $q->where('company_id', $company->id); })
            ->orderByDesc('id')
            ->paginate(12);

        return Inertia::render('ServiceDashboard/Cases/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'cases' => $cases,
        ]);
    }

    public function create(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $services = Service::where('company_id', $company->id)->get(['id','title']);
        return Inertia::render('ServiceDashboard/Cases/Create', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'services' => $services,
        ]);
    }

    public function edit(Request $request, ServiceCase $case)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($case->service && $case->service->company_id === $company->id, 403);
        $services = Service::where('company_id', $company->id)->get(['id','title']);
        return Inertia::render('ServiceDashboard/Cases/Edit', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
            'case' => $case,
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'equipment_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'photos.*' => 'nullable|image|max:5120',
            'pdfs.*' => 'nullable|file|mimetypes:application/pdf|max:10240',
        ]);

        $service = Service::findOrFail($data['service_id']);
        abort_unless($service->company_id === $company->id, 403);

        $files = [ 'photos' => [], 'pdfs' => [] ];
        foreach (['photos','pdfs'] as $group) {
            if ($request->hasFile($group)) {
                foreach ($request->file($group) as $file) {
                    $path = $file->store("service_cases/{$company->id}/{$group}", 'public');
                    $files[$group][] = $path;
                }
            }
        }

        $case = ServiceCase::create([
            'service_id' => $service->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'equipment_type' => $data['equipment_type'] ?? null,
            'location' => $data['location'] ?? null,
            'files' => $files,
        ]);

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'case_created',
            'context' => [ 'case_id' => $case->id, 'service_id' => $service->id ],
        ]);

        return redirect()->route('service.cases.index')->with('success', 'Case added to portfolio.');
    }

    public function update(Request $request, ServiceCase $case)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($case->service && $case->service->company_id === $company->id, 403);

        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'equipment_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'photos.*' => 'nullable|image|max:5120',
            'pdfs.*' => 'nullable|file|mimetypes:application/pdf|max:10240',
            'remove_photos' => 'array',
            'remove_photos.*' => 'string',
            'remove_pdfs' => 'array',
            'remove_pdfs.*' => 'string',
        ]);

        $service = Service::findOrFail($data['service_id']);
        abort_unless($service->company_id === $company->id, 403);

        $files = $case->files ?? [ 'photos' => [], 'pdfs' => [] ];

        // Prune removed files (if any) from storage and metadata
        foreach (($data['remove_photos'] ?? []) as $path) {
            if (in_array($path, $files['photos'] ?? [])) {
                $files['photos'] = array_values(array_filter($files['photos'], fn($p) => $p !== $path));
                Storage::disk('public')->delete($path);
            }
        }
        foreach (($data['remove_pdfs'] ?? []) as $path) {
            if (in_array($path, $files['pdfs'] ?? [])) {
                $files['pdfs'] = array_values(array_filter($files['pdfs'], fn($p) => $p !== $path));
                Storage::disk('public')->delete($path);
            }
        }

        // Append newly uploaded files
        foreach (['photos','pdfs'] as $group) {
            if ($request->hasFile($group)) {
                foreach ($request->file($group) as $file) {
                    $path = $file->store("service_cases/{$company->id}/{$group}", 'public');
                    $files[$group][] = $path;
                }
            }
        }

        $case->update([
            'service_id' => $service->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'equipment_type' => $data['equipment_type'] ?? null,
            'location' => $data['location'] ?? null,
            'files' => $files,
        ]);

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'case_updated',
            'context' => [ 'case_id' => $case->id, 'service_id' => $service->id ],
        ]);

        return redirect()->route('service.cases.index')->with('success', 'Case updated.');
    }

    public function destroy(Request $request, ServiceCase $case)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($case->service && $case->service->company_id === $company->id, 403);

        // Delete stored files
        $files = $case->files ?? [];
        foreach (['photos','pdfs'] as $group) {
            foreach (($files[$group] ?? []) as $path) {
                if ($path) { \Illuminate\Support\Facades\Storage::disk('public')->delete($path); }
            }
        }

        $caseId = $case->id;
        $serviceId = $case->service_id;
        $case->delete();

        ActivityLog::create([
            'company_id' => $company->id,
            'actor_user_id' => $request->user()->id,
            'action' => 'case_deleted',
            'context' => [ 'case_id' => $caseId, 'service_id' => $serviceId ],
        ]);

        return redirect()->route('service.cases.index')->with('success', 'Case deleted.');
    }
}