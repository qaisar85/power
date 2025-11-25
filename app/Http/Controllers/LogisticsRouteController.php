<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LogisticsRoute;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LogisticsRouteController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->first();
        if (!$company) {
            return Inertia::render('ServiceDashboard/Logistics/Routes/Index', [
                'needs_company' => true,
                'company' => null,
                'routes' => [],
                'services' => [],
            ]);
        }

        $routes = LogisticsRoute::where('company_id', $company->id)->orderByDesc('id')->get();
        $services = Service::where('company_id', $company->id)->where('category','logistics')->get(['id','title']);

        return Inertia::render('ServiceDashboard/Logistics/Routes/Index', [
            'needs_company' => false,
            'company' => $company,
            'routes' => $routes,
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();

        $data = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'from_country' => 'required|string|max:64',
            'from_city' => 'nullable|string|max:64',
            'to_country' => 'required|string|max:64',
            'to_city' => 'nullable|string|max:64',
            'transport_type' => 'required|string|in:road,air,sea,rail,warehousing,customs',
            'frequency' => 'nullable|string|max:64',
            'timeline_days' => 'nullable|integer|min:0',
            'price_per_kg' => 'nullable|numeric|min:0',
            'price_per_ton' => 'nullable|numeric|min:0',
            'price_per_container' => 'nullable|numeric|min:0',
            'conditions' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $route = new LogisticsRoute(array_merge($data, [
            'company_id' => $company->id,
        ]));
        $route->save();

        // Handle uploaded documents
        if ($request->hasFile('documents')) {
            $docs = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store("logistics/routes/{$route->id}", 'public');
                $docs[] = [
                    'path' => $path,
                    'original' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
            $route->documents = array_merge($route->documents ?? [], $docs);
            $route->save();
        }

        return redirect()->back()->with('success', 'Route added');
    }

    public function update(Request $request, LogisticsRoute $route)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($route->company_id === $company->id, 403);

        $data = $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'from_country' => 'required|string|max:64',
            'from_city' => 'nullable|string|max:64',
            'to_country' => 'required|string|max:64',
            'to_city' => 'nullable|string|max:64',
            'transport_type' => 'required|string|in:road,air,sea,rail,warehousing,customs',
            'frequency' => 'nullable|string|max:64',
            'timeline_days' => 'nullable|integer|min:0',
            'price_per_kg' => 'nullable|numeric|min:0',
            'price_per_ton' => 'nullable|numeric|min:0',
            'price_per_container' => 'nullable|numeric|min:0',
            'conditions' => 'nullable|string',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $route->update($data);

        // Handle appended documents
        if ($request->hasFile('documents')) {
            $docs = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store("logistics/routes/{$route->id}", 'public');
                $docs[] = [
                    'path' => $path,
                    'original' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
            $route->documents = array_merge($route->documents ?? [], $docs);
            $route->save();
        }

        return redirect()->back()->with('success', 'Route updated');
    }

    public function destroy(Request $request, LogisticsRoute $route)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($route->company_id === $company->id, 403);

        // Delete stored files
        foreach (($route->documents ?? []) as $doc) {
            if (!empty($doc['path'])) {
                Storage::disk('public')->delete($doc['path']);
            }
        }

        $route->delete();

        return redirect()->back()->with('success', 'Route deleted');
    }

    public function destroyDocument(Request $request, LogisticsRoute $route, int $index)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        abort_unless($route->company_id === $company->id, 403);
    
        $docs = $route->documents ?? [];
        if (!is_array($docs) || !array_key_exists($index, $docs)) {
            return redirect()->back()->with('error', 'Document not found');
        }
    
        $doc = $docs[$index];
        if (!empty($doc['path'])) {
            Storage::disk('public')->delete($doc['path']);
        }
    
        unset($docs[$index]);
        $route->documents = array_values($docs);
        $route->save();
    
        return redirect()->back()->with('success', 'Document removed');
    }
}