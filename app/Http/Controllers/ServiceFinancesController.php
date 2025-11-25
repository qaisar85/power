<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceFinancesController extends Controller
{
    public function show(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->with('plan')->firstOrFail();
        $plans = Plan::where('is_active', true)->orderBy('price')->get();
        return Inertia::render('ServiceDashboard/Finances/Index', [
            'company' => $company,
            'plans' => $plans,
        ]);
    }

    public function changePlan(Request $request)
    {
        $company = Company::where('user_id', $request->user()->id)->firstOrFail();
        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($data['plan_id']);
        $company->plan_id = $plan->id;
        $company->plan_expires_at = now()->addDays($plan->duration_days ?? 30);
        $company->save();

        return back()->with('success', 'Plan updated.');
    }
}