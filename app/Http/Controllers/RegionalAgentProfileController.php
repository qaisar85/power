<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRegionalAgentProfileRequest;
use App\Models\RegionalAgent;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RegionalAgentProfileController extends Controller
{
    /**
     * Display the regional agent profile management page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        
        // Get or create regional agent profile
        $agent = RegionalAgent::where('user_id', $user->id)->first();
        
        if (!$agent) {
            // Create a basic profile if it doesn't exist
            $agent = RegionalAgent::create([
                'user_id' => $user->id,
                'region_type' => 'city',
                'is_active' => false, // Needs to be verified
            ]);
        }

        $agent->load(['country', 'state', 'city']);

        // Get countries, states, cities for dropdowns
        $countries = Country::active()->orderBy('name')->get(['id', 'name', 'code']);
        $states = $agent->country_id 
            ? State::where('country_id', $agent->country_id)->active()->orderBy('name')->get(['id', 'name'])
            : [];
        $cities = $agent->state_id 
            ? City::where('state_id', $agent->state_id)->active()->orderBy('name')->get(['id', 'name'])
            : [];

        // Service types options
        $serviceTypeOptions = [
            'equipment_listing' => 'Equipment Listing',
            'consultation' => 'Consultation',
            'verification' => 'Verification',
            'logistics' => 'Logistics',
            'inspection' => 'Inspection',
            'repair' => 'Repair Services',
            'training' => 'Training',
            'support' => 'Customer Support',
        ];

        return Inertia::render('RegionalAgent/Profile', [
            'agent' => [
                'id' => $agent->id,
                'business_name' => $agent->business_name,
                'business_description' => $agent->business_description,
                'business_license' => $agent->business_license,
                'region_type' => $agent->region_type,
                'country_id' => $agent->country_id,
                'state_id' => $agent->state_id,
                'city_id' => $agent->city_id,
                'latitude' => $agent->latitude,
                'longitude' => $agent->longitude,
                'service_types' => $agent->service_types ?? [],
                'supported_categories' => $agent->supported_categories ?? [],
                'languages' => $agent->languages ?? [],
                'certifications' => $agent->certifications ?? [],
                'office_address' => $agent->office_address,
                'office_phone' => $agent->office_phone,
                'office_email' => $agent->office_email,
                'office_hours' => $agent->office_hours ?? [],
                'working_hours' => $agent->working_hours ?? [],
                'timezone' => $agent->timezone,
                'logo' => $agent->logo ? Storage::url($agent->logo) : null,
                'video_resume_url' => $agent->video_resume_url ? Storage::url($agent->video_resume_url) : null,
                'is_verified' => $agent->is_verified,
                'is_active' => $agent->is_active,
                'performance_rating' => $agent->performance_rating,
                'total_services_completed' => $agent->total_services_completed,
            ],
            'countries' => $countries,
            'states' => $states,
            'cities' => $cities,
            'serviceTypeOptions' => $serviceTypeOptions,
        ]);
    }

    /**
     * Update the regional agent profile.
     */
    public function update(UpdateRegionalAgentProfileRequest $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        
        // Get or create regional agent profile
        $agent = RegionalAgent::where('user_id', $user->id)->firstOrFail();

        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($agent->logo && Storage::exists($agent->logo)) {
                Storage::delete($agent->logo);
            }
            
            $data['logo'] = $request->file('logo')->store(
                "regional-agents/{$agent->id}/logo",
                'public'
            );
        }

        // Handle video resume upload
        if ($request->hasFile('video_resume')) {
            // Delete old video if exists
            if ($agent->video_resume_url && Storage::exists($agent->video_resume_url)) {
                Storage::delete($agent->video_resume_url);
            }
            
            $data['video_resume_url'] = $request->file('video_resume')->store(
                "regional-agents/{$agent->id}/video-resume",
                'public'
            );
        }

        // Remove file inputs from data array (they're already handled)
        unset($data['logo'], $data['video_resume']);

        // Update the agent
        $agent->update($data);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Get states for a country (AJAX).
     */
    public function getStates(Request $request, $country): \Illuminate\Http\JsonResponse
    {
        $countryModel = Country::findOrFail($country);
        $states = State::where('country_id', $countryModel->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($states);
    }

    /**
     * Get cities for a state (AJAX).
     */
    public function getCities(Request $request, $state): \Illuminate\Http\JsonResponse
    {
        $stateModel = State::findOrFail($state);
        $cities = City::where('state_id', $stateModel->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }
}
