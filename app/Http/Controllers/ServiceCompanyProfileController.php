<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceCompanyProfileController extends Controller
{
    public function show(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->first();
        return Inertia::render('ServiceDashboard/Profile/Index', [
            'company' => $company,
        ]);
    }

    public function update(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|array',
            'logo' => 'nullable|file|image|max:5120',
            'banner' => 'nullable|file|image|max:8192',
            'sectors' => 'nullable|array',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('company/logo/'.$company->id, 'public');
        }
        if ($request->hasFile('banner')) {
            $data['banner'] = $request->file('banner')->store('company/banner/'.$company->id, 'public');
        }

        $company->update($data);
        return back()->with('success', 'Company profile updated.');
    }
}