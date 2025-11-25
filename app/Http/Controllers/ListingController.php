<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Listing;
use App\Models\ListingRequest;
use App\Models\ModerationTask;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ShippingRequestMail;
use App\Notifications\ShippingRequestNotification;

class ListingController extends Controller
{
    public function create()
    {
        $user = auth()->guard('web')->user();
        $roles = $user && method_exists($user, 'getRoleNames') ? $user->getRoleNames() : collect();

        $categories = [
            'Drilling Equipment', 'Pumps', 'Compressors', 'Valves', 'Pipes', 'Generators', 'Heavy Machinery' , 'Textile Machinery' , 'Other Machinery' , 'Used Machinery' , 'Agricultural Machinery' , 'Others'
        ];

        $packages = [
            ['key' => 'test', 'name' => 'Test Package', 'price' => 0, 'desc' => 'Limited test publishing'],
            ['key' => 'pkg1', 'name' => 'Package 1', 'price' => 49, 'desc' => 'Basic publishing'],
            ['key' => 'pkg2', 'name' => 'Package 2', 'price' => 99, 'desc' => 'Extended reach'],
            ['key' => 'vip', 'name' => 'VIP Package', 'price' => 199, 'desc' => 'Priority placement'],
            ['key' => 'regional_manager', 'name' => 'Regional Manager Services', 'price' => 499, 'desc' => 'Managed posting & support'],
        ];

        return Inertia::render('Listings/Wizard', [
            'roles' => $roles,
            'categories' => $categories,
            'packages' => $packages,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role' => 'nullable|string',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'location' => 'nullable|string|max:255',
            'deal_type' => 'required|string',
            'payment_options' => 'array',
            'category' => 'nullable|string|max:255',
            'subcategories' => 'array',
            'publish_in_rent' => 'boolean',
            'publish_in_auction' => 'boolean',
            'rent_fields' => 'array',
            'rent_fields.documents' => 'array',
            'rent_fields.documents.*' => 'file|mimes:pdf,doc,docx|max:5120',
            'auction_fields' => 'array',
            'auction_fields.min_step' => 'nullable|numeric',
            'auction_fields.buy_now_price' => 'nullable|numeric',
            'auction_fields.time_limit_minutes' => 'nullable|integer',
            'logistics_fields' => 'array',
            'product_fields' => 'array',
            'photos' => 'array',
            'photos.*' => 'file|mimes:jpg,jpeg,png,mp4|max:5120',
            'preview_comment' => 'nullable|string',
            'package' => 'nullable|string',
        ]);

        // Handle photo uploads (limit by package)
        $packageKey = $data['package'] ?? 'basic';
        $maxPhotosByPackage = [
            'test' => 10,
            'basic' => 10,
            'pkg1' => 20,
            'pkg2' => 50,
            'vip' => 100,
            'regional_manager' => 200,
        ];
        $maxPhotos = $maxPhotosByPackage[$packageKey] ?? 10;
        $uploaded = $request->file('photos', []);
        if (is_array($uploaded) && count($uploaded) > 0) {
            $uploaded = array_slice($uploaded, 0, $maxPhotos);
        }
        $photoPaths = [];
        foreach ($uploaded as $file) {
            try {
                $path = $file->store('listings', 'public');
                $photoPaths[] = Storage::url($path);
            } catch (\Throwable $e) {
                // Swallow individual upload errors; log later if needed
                continue;
            }
        }
        if (!empty($photoPaths)) {
            $data['photos'] = $photoPaths;
        }

        // Handle rent documents uploads (contracts/terms PDFs)
        $rentDocs = $request->file('rent_fields.documents', []);
        if (is_array($rentDocs) && count($rentDocs) > 0) {
            $rentDocPaths = [];
            foreach ($rentDocs as $docFile) {
                try {
                    $docPath = $docFile->store('rent_documents', 'public');
                    $rentDocPaths[] = Storage::url($docPath);
                } catch (\Throwable $e) {
                    continue;
                }
            }
            $data['rent_fields'] = array_merge($data['rent_fields'] ?? [], [
                'documents' => $rentDocPaths,
            ]);
        }

        $listing = Listing::create(array_merge($data, [
            'user_id' => $request->user()->id,
            'status' => 'under_review',
        ]));

        // Create a moderation task for this listing submission
        $package = $data['package'] ?? null;
        $priority = null;
        if (in_array($package, ['vip', 'regional_manager'], true)) {
            $priority = 2;
        } elseif ($package === 'pkg2') {
            $priority = 1;
        }
        // Elevate priority if listing will also be published in auction
        if (($data['publish_in_auction'] ?? false) && ($priority === null || $priority < 1)) {
            $priority = 1;
        }

        // Derive country and region from location for moderation routing
        $location = $data['location'] ?? '';
        $country = null;
        $region = null;
        if ($location) {
            $parts = preg_split('/[,\-]/', $location);
            $parts = array_map('trim', $parts);
            $parts = array_filter($parts);
            if (count($parts) >= 1) {
                $country = $parts[count($parts) - 1];
            }
            if (count($parts) >= 2) {
                $region = $parts[count($parts) - 2];
            }
        }

        ModerationTask::create([
            'item_type' => $data['deal_type'] ?? 'sale',
            'item_id' => $listing->id,
            'submitted_by' => $request->user()->id,
            'status' => 'pending',
            'comment' => $data['preview_comment'] ?? null,
            'priority' => $priority ?? 0,
            'country' => $country,
            'region' => $region,
        ]);

        return redirect()->route('account')->with([
            'success' => 'Listing submitted for moderation. After publish, you can post a free Tender to buy or request services.',
            'suggestTender' => true,
        ]);
    }

    public function show(Listing $listing)
    {
        $viewer = auth()->guard('web')->user();

        // Combined gating: listing or viewer membership unlocks contacts
        $listingPackage = $listing->package ?? 'basic';
        $viewerPackage = $viewer?->user_package ?? 'basic';
        $unlockedPackages = ['professional', 'vip', 'regional_manager'];
        $viewerHasContactAccess = in_array($viewerPackage, $unlockedPackages);
        $placementPaid = (($listing->product_fields['placement_type'] ?? 'free') === 'paid');
        $canViewContacts = $viewerHasContactAccess || $placementPaid || ($listingPackage !== 'basic');
        $isOwner = $viewer && $viewer->id === $listing->user_id;

        return Inertia::render('Listings/Show', [
            'listing' => [
                'id' => $listing->id,
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'currency' => $listing->currency,
                'location' => $listing->location,
                'deal_type' => $listing->deal_type,
                'category' => $listing->category,
                'subcategories' => $listing->subcategories ?? [],
                'photos' => $listing->photos ?? [],
                'status' => $listing->status,
                'package' => $listingPackage,
                'product_fields' => $listing->product_fields ?? [],
                'payment_options' => $listing->payment_options ?? [],
                'seller' => [
                    'name' => $listing->user->name,
                    'email' => $listing->user->email,
                    'phone' => $listing->user->phone ?? 'N/A',
                ],
                'canViewContacts' => $canViewContacts,
                'isOwner' => $isOwner,
            ],
        ]);
    }

    public function request(Request $request, Listing $listing)
    {
        $user = $request->user();

        $data = $request->validate([
            'type' => 'required|in:contact,inspection,shipping,subscribe',
            'message' => 'nullable|string',
            'meta' => 'array',
        ]);

        $listingRequest = ListingRequest::create([
            'listing_id' => $listing->id,
            'user_id' => $user->id,
            'type' => $data['type'],
            'message' => $data['message'] ?? null,
            'meta' => $data['meta'] ?? [],
            'status' => 'new',
        ]);

        // Trigger notifications for shipping requests
        if ($data['type'] === 'shipping') {
            try {
                $owner = $listing->user;
                $logisticsEmail = config('services.logistics.email');

                $mail = new ShippingRequestMail(
                    $listing,
                    $user,
                    $data['message'] ?? null,
                    $data['meta'] ?? []
                );

                // Email listing owner
                if ($owner && $owner->email) {
                    Mail::to($owner->email)->send($mail);
                    // In-app notification for owner
                    $owner->notify(new ShippingRequestNotification(
                        $listing,
                        $user,
                        $data['message'] ?? null,
                        $data['meta'] ?? []
                    ));
                }

                // Email logistics
                if (!empty($logisticsEmail)) {
                    Mail::to($logisticsEmail)->send($mail);
                }
            } catch (\Throwable $e) {
                Log::error('Shipping request notification failed', [
                    'listing_id' => $listing->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Request submitted');
    }

    public function sample()
    {
        $user = request()->user();
        $listing = Listing::first();
        if (!$listing) {
            $listing = Listing::create([
                'user_id' => $user->id,
                'role' => 'company',
                'type' => 'product',
                'title' => 'Sample Pump Model X100',
                'description' => 'High-efficiency industrial pump suitable for oil & gas operations.',
                'price' => 12000,
                'currency' => 'USD',
                'status' => 'published',
                'location' => 'Houston, TX',
                'deal_type' => 'sale',
                'payment_options' => ['bank_transfer', 'escrow'],
                'category' => 'Pumps',
                'subcategories' => ['Centrifugal'],
                'photos' => [],
                'package' => 'basic',
                'product_fields' => [
                    'condition' => 'used',
                    'manufacturer' => 'ACME Pumps',
                    'model' => 'X100',
                    'year' => '2019',
                    'availability_status' => 'in_stock',
                    'placement_type' => 'paid',
                    'placement_term' => 30,
                ],
            ]);
        }

        return redirect()->route('listings.show', $listing);
    }

    public function upgrade(Request $request, Listing $listing)
    {
        $user = $request->user();
        abort_unless($user && $user->id === $listing->user_id, 403);

        $data = $request->validate([
            'package' => 'required|in:professional,vip,regional_manager',
        ]);

        $listing->update(['package' => $data['package']]);

        return back()->with('success', 'Listing upgraded to ' . $data['package']);
    }
}