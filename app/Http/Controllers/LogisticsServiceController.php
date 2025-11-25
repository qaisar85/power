<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogisticsServiceController extends Controller
{
    public function index(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->first();
        if (!$company) {
            return Inertia::render('ServiceDashboard/Logistics/Services/Index', [
                'needs_company' => true,
                'company' => null,
                'services' => [],
            ]);
        }

        $services = Service::where('company_id', $company->id)
            ->where('category', 'logistics')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('ServiceDashboard/Logistics/Services/Index', [
            'needs_company' => false,
            'company' => $company,
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subcategory' => 'required|string|in:road,air,sea,rail,warehousing,customs',
            'price_type' => 'required|string|in:fixed,range,hourly,formula',
            'price_value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'geo' => 'nullable|array',
            'visibility' => 'nullable|string|in:pending,published,hidden',
        ]);

        $service = new Service(array_merge($data, [
            'company_id' => $company->id,
            'category' => 'logistics',
            'service_type' => 'other',
            'placement_type' => 'free',
        ]));

        $service->save();

        return redirect()->back()->with('success', 'Service created');
    }
}