<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $services = Service::where('company_id', $company->id)
            ->orderByDesc('id')
            ->paginate(15);

        return Inertia::render('ServiceDashboard/Services/Index', [
            'company' => [ 'id' => $company->id, 'name' => $company->name, 'is_verified' => (bool) $company->is_verified ],
            'services' => $services,
        ]);
    }

    public function create(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        return Inertia::render('ServiceDashboard/Services/Create', [
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
        ]);
    }

    public function store(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'service_type' => 'nullable|string|max:50',
            'price_type' => 'nullable|string|max:50',
            'price_value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'price_details' => 'nullable|string',
            'geo' => 'nullable|array',
            'placement_type' => 'nullable|string|max:20',
            'visibility' => 'nullable|string|max:20',
            'photos.*' => 'nullable|image|max:5120',
            'videos.*' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:51200',
            'pdfs.*' => 'nullable|file|mimetypes:application/pdf|max:10240',
        ]);

        $files = [ 'photos' => [], 'videos' => [], 'pdfs' => [] ];
        foreach (['photos','videos','pdfs'] as $group) {
            if ($request->hasFile($group)) {
                foreach ($request->file($group) as $file) {
                    $path = $file->store("services/{$company->id}/{$group}", 'public');
                    $files[$group][] = $path;
                }
            }
        }

        $service = Service::create([
            'company_id' => $company->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'subcategory' => $data['subcategory'] ?? null,
            'service_type' => $data['service_type'] ?? null,
            'price_type' => $data['price_type'] ?? null,
            'price_value' => $data['price_value'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'price_details' => $data['price_details'] ?? null,
            'geo' => $data['geo'] ?? [],
            'files' => $files,
            'placement_type' => $data['placement_type'] ?? 'free',
            'visibility' => $data['visibility'] ?? 'pending',
        ]);

        return redirect()->route('service.services.index')->with('success', 'Service created and sent for moderation.');
    }

    public function edit(Request $request, Service $service)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($service->company_id === $company->id, 403);

        return Inertia::render('ServiceDashboard/Services/Edit', [
            'service' => $service,
            'company' => [ 'id' => $company->id, 'name' => $company->name ],
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($service->company_id === $company->id, 403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'subcategory' => 'nullable|string|max:100',
            'service_type' => 'nullable|string|max:50',
            'price_type' => 'nullable|string|max:50',
            'price_value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:10',
            'price_details' => 'nullable|string',
            'geo' => 'nullable|array',
            'placement_type' => 'nullable|string|max:20',
            'visibility' => 'nullable|string|max:20',
            'photos.*' => 'nullable|image|max:5120',
            'videos.*' => 'nullable|file|mimetypes:video/mp4,video/quicktime|max:51200',
            'pdfs.*' => 'nullable|file|mimetypes:application/pdf|max:10240',
        ]);

        $files = $service->files ?: [ 'photos' => [], 'videos' => [], 'pdfs' => [] ];
        foreach (['photos','videos','pdfs'] as $group) {
            if ($request->hasFile($group)) {
                foreach ($request->file($group) as $file) {
                    $path = $file->store("services/{$company->id}/{$group}", 'public');
                    $files[$group][] = $path;
                }
            }
        }

        $service->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'subcategory' => $data['subcategory'] ?? null,
            'service_type' => $data['service_type'] ?? null,
            'price_type' => $data['price_type'] ?? null,
            'price_value' => $data['price_value'] ?? null,
            'currency' => $data['currency'] ?? $service->currency,
            'price_details' => $data['price_details'] ?? null,
            'geo' => $data['geo'] ?? $service->geo,
            'files' => $files,
            'placement_type' => $data['placement_type'] ?? $service->placement_type,
            'visibility' => $data['visibility'] ?? $service->visibility,
        ]);

        return redirect()->route('service.services.index')->with('success', 'Service updated.');
    }

    public function destroy(Request $request, Service $service)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($service->company_id === $company->id, 403);

        try {
            $service->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to delete service', ['e' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete service.');
        }
        return redirect()->route('service.services.index')->with('success', 'Service deleted.');
    }

    public function publish(Request $request, Service $service)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($service->company_id === $company->id, 403);

        if (!$company->is_verified) {
            return back()->with('error', 'Only verified companies can publish services.');
        }

        $service->update(['visibility' => 'published']);
        return back()->with('success', 'Service published.');
    }
}