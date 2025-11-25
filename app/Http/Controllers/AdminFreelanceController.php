<?php

namespace App\Http\Controllers;

use App\Models\FreelanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminFreelanceController extends Controller
{
    public function index(Request $request)
    {
        $services = FreelanceService::query()
            ->orderByDesc('created_at')
            ->paginate(30);

        return Inertia::render('Admin/Freelance/Index', [
            'services' => $services,
        ]);
    }

    public function approve(Request $request, FreelanceService $service)
    {
        $service->status = 'approved';
        $service->save();
        return redirect()->back()->with('success', 'Service approved');
    }

    public function reject(Request $request, FreelanceService $service)
    {
        $service->status = 'rejected';
        $service->save();
        return redirect()->back()->with('success', 'Service rejected');
    }

    public function feature(Request $request, FreelanceService $service)
    {
        $packages = $service->packages ?? [];
        $meta = $packages;
        $service->packages = $meta;
        $service->setAttribute('featured', true);
        $service->save();
        return redirect()->back()->with('success', 'Service featured');
    }
}

