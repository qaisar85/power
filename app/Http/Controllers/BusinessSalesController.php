<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\BusinessSubsector;
use App\Models\ListingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BusinessSalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query()
            ->whereNotNull('business_fields')
            ->where('status', 'published')
            ->orderByDesc('created_at');

        // Basic filters
        $country = $request->string('country')->toString();
        $city = $request->string('city')->toString();
        $subcategory = $request->string('subcategory')->toString();
        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');
        $turnoverMin = $request->input('turnover_min');
        $turnoverMax = $request->input('turnover_max');
        $status = $request->string('status')->toString();
        $hasAssets = $request->boolean('assets');
        $hasRealEstate = $request->boolean('real_estate');
        $hasLicenses = $request->boolean('licenses');
        $hasExpansion = $request->boolean('expansion');
        $search = $request->string('search')->toString();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(business_fields, '$.short_description'))"), 'like', "%$search%")
                  ->orWhere(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(business_fields, '$.full_description'))"), 'like', "%$search%");
            });
        }

        if ($country) {
            $query->where(function ($q) use ($country) {
                $q->where('location', 'like', "%$country%")
                  ->orWhere(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(business_fields, '$.country'))"), 'like', "%$country%");
            });
        }
        if ($city) {
            $query->where(function ($q) use ($city) {
                $q->where('location', 'like', "%$city%")
                  ->orWhere(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(business_fields, '$.city'))"), 'like', "%$city%");
            });
        }
        if ($subcategory) {
            $query->whereJsonContains('subcategories', $subcategory);
        }
        if ($priceMin !== null) {
            $query->where(function ($q) use ($priceMin) {
                $q->where('price', '>=', $priceMin)
                  ->orWhere(DB::raw("JSON_EXTRACT(business_fields, '$.target_price')"), '>=', $priceMin);
            });
        }
        if ($priceMax !== null) {
            $query->where(function ($q) use ($priceMax) {
                $q->where('price', '<=', $priceMax)
                  ->orWhere(DB::raw("JSON_EXTRACT(business_fields, '$.target_price')"), '<=', $priceMax);
            });
        }
        if ($turnoverMin !== null) {
            $query->where(DB::raw("JSON_EXTRACT(business_fields, '$.annual_turnover')"), '>=', $turnoverMin);
        }
        if ($turnoverMax !== null) {
            $query->where(DB::raw("JSON_EXTRACT(business_fields, '$.annual_turnover')"), '<=', $turnoverMax);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($hasAssets) {
            $query->where(DB::raw("JSON_EXTRACT(business_fields, '$.assets_available')"), true);
        }
        if ($hasRealEstate) {
            $query->where(DB::raw("JSON_EXTRACT(business_fields, '$.includes_real_estate')"), true);
        }
        if ($hasLicenses) {
            $query->where(DB::raw("JSON_EXTRACT(business_fields, '$.licenses_available')"), true);
        }
        if ($hasExpansion) {
            $query->where(DB::raw("JSON_EXTRACT(business_fields, '$.expansion_potential')"), true);
        }

        $listings = $query->paginate(12)->through(function ($l) {
            $bf = $l->business_fields ?? [];
            return [
                'id' => $l->id,
                'title' => $l->title,
                'location' => $l->location,
                'subcategory' => $l->subcategories[0] ?? null,
                'price' => $l->price ?? ($bf['target_price'] ?? null),
                'currency' => $l->currency ?? ($bf['currency'] ?? 'USD'),
                'shortDescription' => $bf['short_description'] ?? null,
                'badges' => [
                    'includesRealEstate' => (bool)($bf['includes_real_estate'] ?? false),
                    'licenses' => (bool)($bf['licenses_available'] ?? false),
                    'inventory' => (bool)($bf['inventory'] ?? false),
                    'expansion' => (bool)($bf['expansion_potential'] ?? false),
                ],
                'photo' => ($l->photos[0] ?? null),
            ];
        });

        $subcategories = BusinessSubsector::active()->orderBy('sort_order')->get(['slug', 'name']);

        $seo = [
            'title' => 'Business for Sale – Oil & Gas Companies',
            'description' => 'Browse oil and gas businesses for sale. Filter by location, type, and financials.',
            'h1' => 'Business for Sale',
        ];

        return Inertia::render('BusinessSales/Index', [
            'listings' => $listings,
            'filters' => [
                'country' => $country,
                'city' => $city,
                'subcategory' => $subcategory,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'turnover_min' => $turnoverMin,
                'turnover_max' => $turnoverMax,
                'status' => $status,
                'assets' => $hasAssets,
                'real_estate' => $hasRealEstate,
                'licenses' => $hasLicenses,
                'expansion' => $hasExpansion,
                'search' => $search,
            ],
            'subcategories' => $subcategories,
            'seo' => $seo,
        ]);
    }

    public function show(Listing $listing)
    {
        abort_unless($listing->business_fields, 404);
        $bf = $listing->business_fields ?? [];

        $payload = [
            'id' => $listing->id,
            'title' => $listing->title,
            'location' => $listing->location,
            'subcategory' => $listing->subcategories[0] ?? null,
            'status' => $listing->status,
            'price' => $listing->price ?? ($bf['target_price'] ?? null),
            'currency' => $listing->currency ?? ($bf['currency'] ?? 'USD'),
            'shortDescription' => $bf['short_description'] ?? null,
            'fullDescription' => $bf['full_description'] ?? null,
            'reasonForSale' => $bf['reason_for_sale'] ?? null,
            'growthPotential' => $bf['growth_potential'] ?? null,
            'financials' => [
                'annualTurnover' => $bf['annual_turnover'] ?? null,
                'profit' => $bf['profit'] ?? null,
                'assetsAvailable' => (bool)($bf['assets_available'] ?? false),
                'assetsDescription' => $bf['assets_description'] ?? null,
                'inventory' => (bool)($bf['inventory'] ?? false),
                'includesRealEstate' => (bool)($bf['includes_real_estate'] ?? false),
                'negotiable' => (bool)($bf['negotiable'] ?? false),
                'financialRatios' => $bf['financial_ratios'] ?? null,
            ],
            'operational' => [
                'employees' => $bf['employees'] ?? null,
                'licensesAvailable' => (bool)($bf['licenses_available'] ?? false),
                'licenseType' => $bf['license_type'] ?? null,
                'wells' => $bf['number_of_wells'] ?? null,
                'avgDailyProduction' => $bf['avg_daily_production'] ?? null,
                'geolocation' => $bf['geolocation'] ?? null,
                'licenseValidity' => $bf['license_validity_period'] ?? null,
                'legalForm' => $bf['legal_form'] ?? null,
                'registrationNumber' => $bf['company_registration_number'] ?? null,
            ],
            'sale' => [
                'type' => $bf['sale_type'] ?? null,
                'buyerType' => $bf['buyer_type'] ?? null,
                'debtsCases' => $bf['debts_cases'] ?? null,
            ],
            'investment' => [
                'roiPotential' => $bf['roi_potential'] ?? null,
                'participationTerms' => $bf['participation_terms'] ?? null,
                'investmentTimeline' => $bf['investment_timeline'] ?? null,
                'expansionPotential' => (bool)($bf['expansion_potential'] ?? false),
                'expansionDescription' => $bf['expansion_description'] ?? null,
            ],
            'media' => [
                'photos' => $listing->photos ?? [],
                'videos' => $bf['videos'] ?? [],
                'documents' => $listing->documents ?? [],
            ],
            'contacts' => [
                'name' => $bf['contact_name'] ?? null,
                'phone' => $bf['contact_phone'] ?? null,
                'email' => $bf['contact_email'] ?? null,
                'public' => (bool)($bf['contact_public'] ?? false),
                'methods' => $bf['contact_methods'] ?? ['chat'],
            ],
        ];

        $seo = [
            'title' => ($listing->title . ' – Business for Sale'),
            'description' => substr(strip_tags($payload['shortDescription'] ?? $payload['fullDescription'] ?? ''), 0, 160),
            'h1' => $listing->title,
        ];

        return Inertia::render('BusinessSales/Show', [
            'listing' => $payload,
            'seo' => $seo,
        ]);
    }

    public function create()
    {
        $subcategories = BusinessSubsector::active()->orderBy('sort_order')->get(['slug', 'name']);
        return Inertia::render('BusinessSales/Create', [
            'subcategories' => $subcategories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer'],
            'ownership_type' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'full_description' => ['nullable', 'string'],
            'reason_for_sale' => ['nullable', 'string'],
            'growth_potential' => ['nullable', 'string'],
            'annual_turnover' => ['nullable', 'numeric'],
            'profit' => ['nullable', 'numeric'],
            'assets_available' => ['nullable', 'boolean'],
            'assets_description' => ['nullable', 'string'],
            'inventory' => ['nullable', 'boolean'],
            'includes_real_estate' => ['nullable', 'boolean'],
            'target_price' => ['nullable', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'negotiable' => ['nullable', 'boolean'],
            'financial_ratios' => ['nullable', 'string'],
            'employees' => ['nullable', 'integer'],
            'licenses_available' => ['nullable', 'boolean'],
            'license_type' => ['nullable', 'string'],
            'number_of_wells' => ['nullable', 'integer'],
            'avg_daily_production' => ['nullable', 'numeric'],
            'geolocation' => ['nullable', 'string'],
            'license_validity_period' => ['nullable', 'string'],
            'legal_form' => ['nullable', 'string'],
            'company_registration_number' => ['nullable', 'string'],
            'sale_type' => ['nullable', 'string'],
            'buyer_type' => ['nullable', 'string'],
            'debts_cases' => ['nullable', 'string'],
            'roi_potential' => ['nullable', 'numeric'],
            'participation_terms' => ['nullable', 'string'],
            'investment_timeline' => ['nullable', 'string'],
            'expansion_potential' => ['nullable', 'boolean'],
            'expansion_description' => ['nullable', 'string'],
            'videos' => ['nullable', 'array'],
            'videos.*' => ['nullable', 'url'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:100'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_public' => ['nullable', 'boolean'],
            'contact_methods' => ['nullable', 'array'],
            'contact_methods.*' => ['nullable', 'string'],
            'photos.*' => ['nullable', 'image', 'max:4096'],
            'documents.*' => ['nullable', 'file', 'max:8192'],
        ]);

        $location = collect([$validated['city'] ?? null, $validated['region'] ?? null, $validated['country'] ?? null])
            ->filter()->implode(', ');

        $listing = new Listing();
        $listing->user_id = auth()->id();
        $listing->type = 'business';
        $listing->deal_type = 'sale';
        $listing->title = $validated['title'];
        $listing->description = $validated['short_description'] ?? ($validated['full_description'] ?? null);
        $listing->location = $location ?: null;
        $listing->currency = $validated['currency'] ?? 'USD';
        $listing->status = 'under_review';
        $listing->subcategories = $validated['subcategory'] ? [$validated['subcategory']] : [];

        $bf = $validated;
        unset($bf['title'], $bf['subcategory']);
        $listing->business_fields = $bf;
        $listing->price = $validated['target_price'] ?? null;

        // Handle photos
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file) {
                    $photos[] = $file->store('businesses/photos', 'public');
                }
            }
        }
        $listing->photos = $photos;

        // Handle documents
        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                if ($file) {
                    $documents[] = $file->store('businesses/documents', 'public');
                }
            }
        }
        $listing->documents = $documents;

        $listing->save();

        return redirect()->route('business.index')->with('success', 'Business submitted for review.');
    }

    public function edit(Listing $listing)
    {
        // Only owner can edit
        abort_unless(auth()->check() && auth()->id() === $listing->user_id, 403);
        abort_unless($listing->business_fields, 404);
        $subcategories = BusinessSubsector::active()->orderBy('sort_order')->get(['slug', 'name']);
        return Inertia::render('BusinessSales/Edit', [
            'listing' => $listing,
            'subcategories' => $subcategories,
        ]);
    }

    public function update(Request $request, Listing $listing)
    {
        // Only owner can update
        abort_unless(auth()->check() && auth()->id() === $listing->user_id, 403);
        abort_unless($listing->business_fields, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'full_description' => ['nullable', 'string'],
            'target_price' => ['nullable', 'numeric'],
            'currency' => ['nullable', 'string', 'max:10'],
            'videos' => ['nullable', 'array'],
            'videos.*' => ['nullable', 'url'],
            'photos.*' => ['nullable', 'image', 'max:4096'],
            'documents.*' => ['nullable', 'file', 'max:8192'],
        ]);

        $location = collect([$validated['city'] ?? null, $validated['region'] ?? null, $validated['country'] ?? null])
            ->filter()->implode(', ');

        $listing->title = $validated['title'];
        $listing->location = $location ?: null;
        $listing->currency = $validated['currency'] ?? $listing->currency;
        $listing->subcategories = $validated['subcategory'] ? [$validated['subcategory']] : ($listing->subcategories ?? []);
        $listing->price = $validated['target_price'] ?? $listing->price;

        $bf = $listing->business_fields ?? [];
        foreach ($validated as $k => $v) {
            if (!in_array($k, ['title', 'subcategory'])) {
                $bf[$k] = $v;
            }
        }
        $listing->business_fields = $bf;

        // Photos
        if ($request->hasFile('photos')) {
            $photos = $listing->photos ?? [];
            foreach ($request->file('photos') as $file) {
                if ($file) {
                    $photos[] = $file->store('businesses/photos', 'public');
                }
            }
            $listing->photos = $photos;
        }

        // Documents
        if ($request->hasFile('documents')) {
            $documents = $listing->documents ?? [];
            foreach ($request->file('documents') as $file) {
                if ($file) {
                    $documents[] = $file->store('businesses/documents', 'public');
                }
            }
            $listing->documents = $documents;
        }

        $listing->save();

        return redirect()->route('business.show', $listing)->with('success', 'Business updated.');
    }

    public function moderate(Request $request, Listing $listing)
    {
        // TODO: lock to admin later; allow owner for now
        abort_unless(auth()->check() && auth()->id() === $listing->user_id, 403);
        $status = $request->string('status')->toString();
        $reason = $request->string('reason')->toString();
        if (in_array($status, ['under_review', 'published', 'rejected'], true)) {
            $listing->status = $status;
            if ($status === 'rejected' && $reason) {
                $listing->preview_comment = $reason;
            }
            $listing->save();
        }
        return back()->with('success', 'Moderation status updated.');
    }

    public function sample()
    {
        $l = Listing::create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'business',
            'deal_type' => 'sale',
            'title' => 'Oilfield Services Company – Middle East',
            'description' => 'Established O&G services provider with recurring contracts.',
            'status' => 'published',
            'currency' => 'USD',
            'price' => 12500000,
            'location' => 'Dubai, UAE',
            'subcategories' => ['oilfield-services-company'],
            'photos' => [],
            'documents' => [],
            'business_fields' => [
                'short_description' => 'Profitable services firm with 200 employees, fleet, and tools.',
                'full_description' => 'Company provides drilling support, logistics, and maintenance services across GCC. Long-term contracts with major operators.',
                'reason_for_sale' => 'Strategic realignment to focus on manufacturing.',
                'growth_potential' => 'High growth with expansion into Saudi & Oman.',
                'annual_turnover' => 45000000,
                'profit' => 6200000,
                'assets_available' => true,
                'assets_description' => 'Fleet of 50 trucks, 30 trailers, 2 workshops.',
                'inventory' => true,
                'includes_real_estate' => true,
                'target_price' => 12500000,
                'currency' => 'USD',
                'negotiable' => true,
                'employees' => 200,
                'licenses_available' => true,
                'license_type' => 'Both',
                'number_of_wells' => 0,
                'avg_daily_production' => null,
                'geolocation' => '25.2048, 55.2708',
                'license_validity_period' => '2025-12-31',
                'legal_form' => 'LLC',
                'company_registration_number' => 'UE-123456',
                'sale_type' => 'Full sale (100%)',
                'buyer_type' => 'Strategic investor',
                'debts_cases' => 'No',
                'roi_potential' => 18,
                'participation_terms' => 'Equity',
                'investment_timeline' => '12-24 months',
                'expansion_potential' => true,
                'expansion_description' => 'Open regional branches and invest in tech.',
                'videos' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
                'contact_name' => 'John Doe',
                'contact_phone' => '+971 50 123 4567',
                'contact_email' => 'john@example.com',
                'contact_public' => false,
                'contact_methods' => ['chat', 'admin'],
            ],
        ]);

        return redirect()->route('business.show', $l->id);
    }
}