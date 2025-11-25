<?php

namespace App\Http\Controllers;

use App\Models\RegionalAgent;
use App\Models\Country;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactsController extends Controller
{
    /**
     * Display the contacts page with 3D globe.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'country' => $request->get('country'),
            'region' => $request->get('region'),
            'manager' => $request->get('manager'),
            'language' => $request->get('language'),
            'role' => $request->get('role'),
        ];

        // Get all active and verified regional agents
        $query = RegionalAgent::with(['user', 'country', 'state', 'city'])
            ->active()
            ->verified();

        // Apply filters
        if ($filters['country']) {
            $query->where('country_id', $filters['country']);
        }

        if ($filters['region']) {
            $query->where('state_id', $filters['region']);
        }

        if ($filters['manager']) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['manager'] . '%');
            });
        }

        if ($filters['language']) {
            $query->whereJsonContains('languages', $filters['language']);
        }

        $agents = $query->get()->map(function ($agent) {
            return [
                'id' => $agent->id,
                'name' => $agent->user->name ?? $agent->business_name,
                'business_name' => $agent->business_name,
                'country' => $agent->country->name ?? null,
                'state' => $agent->state->name ?? null,
                'city' => $agent->city->name ?? null,
                'latitude' => $agent->latitude ?? ($agent->city->latitude ?? null),
                'longitude' => $agent->longitude ?? ($agent->city->longitude ?? null),
                'logo' => $agent->logo ?? $agent->user->profile_photo_url ?? null,
                'video_resume_url' => $agent->video_resume_url,
                'languages' => $agent->languages ?? [],
                'service_types' => $agent->service_types ?? [],
                'performance_rating' => $agent->performance_rating,
                'office_address' => $agent->office_address,
                'office_phone' => $agent->office_phone,
                'office_email' => $agent->office_email,
                'office_hours' => $agent->office_hours,
                'region_coverage' => $agent->region_coverage,
            ];
        })->filter(function ($agent) {
            // Filter out agents without coordinates
            return $agent['latitude'] && $agent['longitude'];
        });

        // Get countries for filter
        $countries = Country::active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get unique languages from agents
        $languages = RegionalAgent::active()
            ->whereNotNull('languages')
            ->get()
            ->pluck('languages')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('Contacts/Index', [
            'agents' => $agents,
            'countries' => $countries,
            'languages' => $languages,
            'filters' => $filters,
            'stats' => [
                'total_agents' => $agents->count(),
                'total_countries' => $agents->pluck('country')->unique()->count(),
            ],
        ]);
    }

    /**
     * Display a single agent/contact profile.
     */
    public function show(RegionalAgent $agent): Response
    {
        $agent->load(['user', 'country', 'state', 'city', 'reviews', 'services']);

        return Inertia::render('Contacts/Show', [
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->user->name ?? $agent->business_name,
                'business_name' => $agent->business_name,
                'business_description' => $agent->business_description,
                'country' => $agent->country->name ?? null,
                'state' => $agent->state->name ?? null,
                'city' => $agent->city->name ?? null,
                'latitude' => $agent->latitude,
                'longitude' => $agent->longitude,
                'logo' => $agent->logo ?? $agent->user->profile_photo_url,
                'video_resume_url' => $agent->video_resume_url,
                'languages' => $agent->languages ?? [],
                'service_types' => $agent->service_types ?? [],
                'performance_rating' => $agent->performance_rating,
                'total_services_completed' => $agent->total_services_completed,
                'office_address' => $agent->office_address,
                'office_phone' => $agent->office_phone,
                'office_email' => $agent->office_email,
                'office_hours' => $agent->office_hours,
                'region_coverage' => $agent->region_coverage,
                'reviews' => $agent->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'user_name' => $review->user->name ?? 'Anonymous',
                        'created_at' => $review->created_at->toDateTimeString(),
                    ];
                }),
            ],
        ]);
    }

    public function message(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255'],
            'message' => ['required','string','max:5000'],
            'recaptcha_token' => ['nullable','string'],
        ]);

        $to = config('mail.from.address');
        if (! $to) {
            $to = env('MAIL_FROM_ADDRESS');
        }

        try {
            \Illuminate\Support\Facades\Mail::raw(
                'Contact message from '.$data['name'].' <'.$data['email'].'>'."\n\n".$data['message'],
                function ($m) use ($data, $to) {
                    $m->to($to)->subject('Contact message');
                }
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send message: '.$e->getMessage());
        }

        return back()->with('success', 'Message sent');
    }
}
