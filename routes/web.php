<?php


use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\TendersController;
use App\Http\Controllers\AuctionsController;
use App\Http\Controllers\BusinessSalesController;
use App\Http\Controllers\DrillingCatalogController;
use App\Http\Controllers\FreelanceServiceController;
use App\Http\Controllers\FreelanceProjectController;

Route::get('/', function () {
    // Get platform statistics
    $stats = \Illuminate\Support\Facades\Cache::remember('home_platform_stats', 3600, function () {
        $companiesCount = \App\Models\Company::where('is_active', true)->count();
        $usersCount = \App\Models\User::where('is_active', true)->count();
        $listingsCount = \App\Models\Listing::where('status', 'published')->count();
        $tendersCount = \App\Models\Tender::where('status', 'published')->count();
        $jobsCount = \App\Models\Job::where('status', '=', 'published')->count();
        $auctionsCount = \App\Models\Listing::where('publish_in_auction', true)
            ->where('status', 'published')
            ->count();
        
        // Count countries from companies and users
        $companiesCountries = \App\Models\Company::where('is_active', true)
            ->whereNotNull('address')
            ->get()
            ->pluck('address')
            ->filter()
            ->map(function ($addr) {
                if (is_array($addr)) {
                    return $addr['country'] ?? null;
                }
                return null;
            })
            ->filter()
            ->unique()
            ->count();
            
        $usersCountries = \App\Models\User::where('is_active', true)
            ->whereNotNull('country')
            ->distinct('country')
            ->count('country');
            
        $totalCountries = max($companiesCountries, $usersCountries, 195); // Fallback to 195
        
        return [
            'companies' => $companiesCount ?: 2100000, // Fallback for demo
            'users' => $usersCount ?: 1500000,
            'listings' => $listingsCount ?: 2100000,
            'tenders' => $tendersCount ?: 12000,
            'jobs' => $jobsCount ?: 450000,
            'auctions' => $auctionsCount ?: 5000,
            'countries' => $totalCountries,
        ];
    });

    $latestProducts = \Illuminate\Support\Facades\Cache::remember('home_latest_products', 600, function () {
        return \App\Models\Listing::query()
            ->where('type', 'product')
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($l) {
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'price' => $l->price,
                    'currency' => $l->currency ?? 'USD',
                    'location' => $l->location,
                    'photo' => ($l->photos[0] ?? null),
                    'category' => $l->category,
                ];
            });
    });

    $latestAuctions = \Illuminate\Support\Facades\Cache::remember('home_latest_auctions', 600, function () {
        return \App\Models\Listing::query()
            ->where('publish_in_auction', true)
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($l) {
                $af = $l->auction_fields ?? [];
                $endAt = $af['end_at'] ?? now()->addDays(3)->toDateTimeString();
                $startPrice = $af['start_price'] ?? ($l->price ?? 0);
                $currentBid = \App\Models\AuctionBid::where('listing_id', $l->id)->orderByDesc('amount')->value('amount') ?? $startPrice;
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'currentBid' => $currentBid,
                    'currency' => $l->currency ?? 'USD',
                    'endAt' => $endAt,
                    'ends_at_ts' => \Carbon\Carbon::parse($endAt)->timestamp * 1000,
                    'photo' => ($l->photos[0] ?? null),
                ];
            });
    });

    // Real categories (Business Sectors + Subsections)
    $categories = \Illuminate\Support\Facades\Cache::remember('home_categories', 600, function () {
        return \App\Models\BusinessSector::active()
            ->orderBy('sort_order')
            ->get()
            ->map(function ($sector) {
                $key = $sector->code ?: $sector->slug;
                $name = \Illuminate\Support\Facades\Lang::has('sectors.' . $key) ? __('sectors.' . $key) : $sector->name;
                return [
                    'name' => $name,
                    'slug' => $sector->slug,
                    'standard' => $sector->standard,
                    'code' => $sector->code,
                    'subs' => $sector->subsectors()->active()->orderBy('sort_order')->get()->map(function ($sub) use ($sector) {
                        $skey = $sub->code ?: $sub->slug;
                        $sname = \Illuminate\Support\Facades\Lang::has('sectors.' . $skey) ? __('sectors.' . $skey) : $sub->name;
                        return [
                            'name' => $sname,
                            'slug' => $sub->slug,
                            'standard' => $sub->standard ?? $sector->standard,
                            'code' => $sub->code,
                        ];
                    })->toArray(),
                ];
            })->toArray();
    });

    // Real countries derived from distinct Listing locations
    $countries = \Illuminate\Support\Facades\Cache::remember('home_countries', 600, function () {
        if (\Illuminate\Support\Facades\Schema::hasTable('countries') && \App\Models\Country::query()->exists()) {
            return \App\Models\Country::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->toArray();
        }

        $locations = \App\Models\Listing::query()
            ->select('location')
            ->whereNotNull('location')
            ->distinct()
            ->limit(500)
            ->pluck('location')
            ->toArray();

        return collect($locations)
            ->map(function ($loc) {
                $parts = array_map('trim', explode(',', (string) $loc));
                return count($parts) ? trim(end($parts)) : trim((string) $loc);
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    });

    $seo = [
        'title' => 'GlobalBiz Platform – Marketplace, Jobs, Tenders, Auctions',
        'description' => 'Connect with global businesses, discover products, join auctions, and grow across 195 countries.',
        'keywords' => 'global marketplace, auctions, tenders, jobs, b2b, international trade',
    ];

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'latestProducts' => $latestProducts,
        'latestAuctions' => $latestAuctions,
        'categories' => $categories,
        'countries' => $countries,
        'stats' => $stats,
        'seo' => $seo,
    ]);
});

Route::get('/locale/{locale}', function (string $locale) {
    $supported = ['en', 'ru', 'ar', 'fr'];
    if (in_array($locale, $supported, true)) {
        \Illuminate\Support\Facades\Session::put('locale', $locale);
    }
    return redirect()->back();
});

// Contacts section with 3D globe
Route::get('/contacts', [\App\Http\Controllers\ContactsController::class, 'index'])->name('contacts.index');
Route::get('/contacts/{agent}', [\App\Http\Controllers\ContactsController::class, 'show'])->name('contacts.show');
Route::post('/contacts/message', [\App\Http\Controllers\ContactsController::class, 'message'])->middleware('recaptcha')->name('contacts.message');

// Regional Agent Profile Management
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role-selected'])->group(function () {
    Route::get('/regional-agent/profile', [\App\Http\Controllers\RegionalAgentProfileController::class, 'show'])->name('regional-agent.profile');
    Route::put('/regional-agent/profile', [\App\Http\Controllers\RegionalAgentProfileController::class, 'update'])->name('regional-agent.profile.update');
    Route::get('/api/countries/{country}/states', [\App\Http\Controllers\RegionalAgentProfileController::class, 'getStates'])->name('api.countries.states');
    Route::get('/api/states/{state}/cities', [\App\Http\Controllers\RegionalAgentProfileController::class, 'getCities'])->name('api.states.cities');
});

// Offline-safe page for PWA fallback
Route::get('/offline', function () {
    return Inertia::render('Offline');
})->name('offline');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role-selected',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/account', [DashboardController::class, 'account'])->name('account');

    Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/connect/{slug}', [\App\Http\Controllers\AdminController::class, 'connect'])->name('admin.connect');
    
    // Listing wizard routes
    Route::get('/listings/new', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings', [ListingController::class, 'store'])->name('listings.store');

    // Listing show & sample
    Route::get('/listings/sample', [ListingController::class, 'sample'])->name('listings.sample');
    Route::get('/listings/{listing}', [ListingController::class, 'show'])->name('listings.show');
    Route::post('/listings/{listing}/request', [ListingController::class, 'request'])->name('listings.request');
    Route::post('/listings/{listing}/upgrade', [ListingController::class, 'upgrade'])->name('listings.upgrade');

    // Favorites endpoints
    Route::post('/listings/{listing}/favorite', [\App\Http\Controllers\FavoritesController::class, 'store'])->name('listings.favorite');
    Route::delete('/listings/{listing}/favorite', [\App\Http\Controllers\FavoritesController::class, 'destroy'])->name('listings.favorite.delete');
    Route::get('/account/favorites', [\App\Http\Controllers\FavoritesController::class, 'index'])->name('account.favorites');

    // Account subpages
    Route::get('/account/messages', [DashboardController::class, 'messages'])->name('account.messages');
    Route::get('/account/listings', [DashboardController::class, 'myListings'])->name('account.listings');
    Route::get('/account/settings', [DashboardController::class, 'settings'])->name('account.settings');

    // Packages & Wallet routes
    Route::get('/packages', [PackagesController::class, 'index'])->name('packages.index');
    Route::post('/packages/subscribe', [PackagesController::class, 'subscribe'])->name('packages.subscribe');
    Route::post('/wallet/topup', [PackagesController::class, 'topup'])->name('wallet.topup');
    Route::post('/wallet/topup/manual', [PackagesController::class, 'topupManual'])->name('wallet.topup.manual');
    // Card payments via Stripe Checkout
    Route::get('/wallet/topup/card', [\App\Http\Controllers\StripePaymentsController::class, 'createCheckout'])
        ->middleware(['auth'])
        ->name('wallet.topup.card');
    Route::get('/wallet/topup/card/success', [\App\Http\Controllers\StripePaymentsController::class, 'success'])
        ->middleware(['auth'])
        ->name('wallet.topup.card.success');
    Route::get('/wallet/topup/card/cancel', [\App\Http\Controllers\StripePaymentsController::class, 'cancel'])
        ->middleware(['auth'])
        ->name('wallet.topup.card.cancel');

    // PayPal card topup
    Route::get('/wallet/topup/paypal', [\App\Http\Controllers\PayPalPaymentsController::class, 'createTopupOrder'])
        ->middleware(['auth'])
        ->name('wallet.topup.paypal');
    Route::get('/wallet/topup/paypal/return', [\App\Http\Controllers\PayPalPaymentsController::class, 'captureReturn'])
        ->middleware(['auth'])
        ->name('wallet.topup.paypal.return');
    Route::get('/wallet/topup/paypal/cancel', [\App\Http\Controllers\PayPalPaymentsController::class, 'cancel'])
        ->middleware(['auth'])
        ->name('wallet.topup.paypal.cancel');
    
    // Module routes
    Route::get('/marketplace', [ModuleController::class, 'show'])->defaults('slug', 'marketplace');
    Route::get('/jobs', [ModuleController::class, 'show'])->defaults('slug', 'jobs');
    Route::get('/tenders', [ModuleController::class, 'show'])->defaults('slug', 'tenders');
    Route::get('/auctions', [ModuleController::class, 'show'])->defaults('slug', 'auctions');
    Route::get('/training', [ModuleController::class, 'show'])->defaults('slug', 'training');
    Route::get('/news', [ModuleController::class, 'show'])->defaults('slug', 'news');
    Route::get('/freelance', [ModuleController::class, 'show'])->defaults('slug', 'freelance');
    
    // Dynamic module routing
    Route::get('/{module:slug}', [ModuleController::class, 'show'])->where('module', '[a-z-]+');
    Route::get('/jobs/new', [JobsController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobsController::class, 'store'])->name('jobs.store');
    Route::get('/tenders/new', [TendersController::class, 'create'])->name('tenders.create');
    Route::post('/tenders', [TendersController::class, 'store'])->name('tenders.store');
    // Auctions routes
    Route::get('/auctions', [AuctionsController::class, 'index'])->name('auctions.index');
    Route::get('/auctions/sample', [AuctionsController::class, 'sample'])->name('auctions.sample');
    Route::get('/auctions/{listing}', [AuctionsController::class, 'show'])->name('auctions.show');
    // Auctions routes
    Route::post('/auctions/{listing}/bid', [AuctionsController::class, 'bid'])->name('auctions.bid');
    Route::post('/auctions/{listing}/buy-now', [AuctionsController::class, 'buyNow'])->name('auctions.buy');
    // Auction final payment via Stripe Checkout
    Route::post('/auctions/{listing}/final-payment/checkout', [AuctionsController::class, 'finalPaymentCheckout'])->name('auctions.final_payment.checkout');
    Route::get('/auctions/{listing}/final-payment/success', [AuctionsController::class, 'finalPaymentSuccess'])->name('auctions.final_payment.success');
    Route::get('/auctions/{listing}/final-payment/cancel', [AuctionsController::class, 'finalPaymentCancel'])->name('auctions.final_payment.cancel');

    // Freelance services
    Route::get('/freelance/services/new', [FreelanceServiceController::class, 'create'])->name('freelance.services.create');
    Route::post('/freelance/services', [FreelanceServiceController::class, 'store'])->name('freelance.services.store');
    Route::get('/freelance/services/{service:slug}', [FreelanceServiceController::class, 'show'])->name('freelance.services.show');

    // Freelance projects
    Route::get('/freelance/projects/new', [FreelanceProjectController::class, 'create'])->name('freelance.projects.create');
    Route::post('/freelance/projects', [FreelanceProjectController::class, 'store'])->name('freelance.projects.store');
    Route::get('/freelance/projects/{project:slug}', [FreelanceProjectController::class, 'show'])->name('freelance.projects.show');
    Route::get('/jobs/new', [JobsController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobsController::class, 'store'])->name('jobs.store');
    Route::get('/tenders/new', [TendersController::class, 'create'])->name('tenders.create');
    Route::post('/tenders', [TendersController::class, 'store'])->name('tenders.store');
    // Auctions routes
    Route::get('/auctions', [AuctionsController::class, 'index'])->name('auctions.index');
    Route::get('/auctions/sample', [AuctionsController::class, 'sample'])->name('auctions.sample');
    Route::get('/auctions/{listing}', [AuctionsController::class, 'show'])->name('auctions.show');
    // Auctions routes
    Route::post('/auctions/{listing}/bid', [AuctionsController::class, 'bid'])->name('auctions.bid');
    Route::post('/auctions/{listing}/buy-now', [AuctionsController::class, 'buyNow'])->name('auctions.buy');
    // Auction final payment via Stripe Checkout
    Route::post('/auctions/{listing}/final-payment/checkout', [AuctionsController::class, 'finalPaymentCheckout'])->name('auctions.final_payment.checkout');
    Route::get('/auctions/{listing}/final-payment/success', [AuctionsController::class, 'finalPaymentSuccess'])->name('auctions.final_payment.success');
    Route::get('/auctions/{listing}/final-payment/cancel', [AuctionsController::class, 'finalPaymentCancel'])->name('auctions.final_payment.cancel');

    // Public preview route for Super Admin dashboard skeleton (will be protected by auth:admin later)
    // Route::get('/super-admin', function () {
    //     return Inertia::render('Admin/Dashboard', [
    //         'stats' => [
    //             'users' => \App\Models\User::count(),
    //             'revenue' => 0,
    //             'activity' => 0,
    //         ],
    //         'sections' => [
    //             'users', 'roles_permissions', 'moderation', 'finance', 'modules_options', 'action_log'
    //         ],
    //     ]);
    // })->name('super-admin.dashboard');
});

// Admin auth routes (login/logout) and protected dashboard & logs
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [\App\Http\Controllers\AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\AdminAuthController::class, 'login'])->name('login.attempt');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', function () {
            $admin = auth()->guard('admin')->user();
            $permissions = optional($admin)->permissions->pluck('name')->values()->all() ?? [];
            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'users' => \App\Models\User::count(),
                    'revenue' => 0,
                    'activity' => 0,
                ],
                'allowedPermissions' => $permissions,
            ]);
        })->name('dashboard');

        Route::get('/logs', [\App\Http\Controllers\AdminLogController::class, 'index'])->name('logs.index');

        // Users management
        Route::get('/users', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/roles', [\App\Http\Controllers\AdminUserController::class, 'showRoles'])->name('users.roles');
        Route::patch('/users/{user}/active', [\App\Http\Controllers\AdminUserController::class, 'toggleActive'])->name('users.active');
        Route::patch('/users/{user}/roles', [\App\Http\Controllers\AdminUserController::class, 'updateRoles'])->name('users.roles.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'destroy'])->name('users.destroy');

        // Roles & Permissions management
        Route::get('/roles', [\App\Http\Controllers\AdminRoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [\App\Http\Controllers\AdminRoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [\App\Http\Controllers\AdminRoleController::class, 'edit'])->name('roles.edit');
        Route::patch('/roles/{role}', [\App\Http\Controllers\AdminRoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\AdminRoleController::class, 'destroy'])->name('roles.destroy');

        Route::post('/permissions', [\App\Http\Controllers\AdminPermissionController::class, 'store'])->name('permissions.store');

        // Finance routes
        Route::get('/finance', [\App\Http\Controllers\AdminFinanceController::class, 'index'])->name('finance.index');
        Route::post('/finance/packages', [\App\Http\Controllers\AdminFinanceController::class, 'storePackage'])->name('finance.packages.store');
        Route::post('/finance/rates', [\App\Http\Controllers\AdminFinanceController::class, 'storeRate'])->name('finance.rates.store');
        Route::post('/finance/promotions', [\App\Http\Controllers\AdminFinanceController::class, 'storePromotion'])->name('finance.promotions.store');
        Route::post('/finance/payment-requests/{paymentRequest}/approve', [\App\Http\Controllers\AdminFinanceController::class, 'approvePayment'])->name('finance.payments.approve');
        Route::post('/finance/payment-requests/{paymentRequest}/reject', [\App\Http\Controllers\AdminFinanceController::class, 'rejectPayment'])->name('finance.payments.reject');
        // Moderation routes
        Route::get('/moderation', [\App\Http\Controllers\AdminModerationController::class, 'index'])->name('moderation.index');
        Route::get('/moderation/report', [\App\Http\Controllers\AdminModerationController::class, 'report'])->name('moderation.report');
        Route::post('/moderation/tasks/{task}/approve', [\App\Http\Controllers\AdminModerationController::class, 'approve'])->name('moderation.tasks.approve');
        Route::post('/moderation/tasks/{task}/decline', [\App\Http\Controllers\AdminModerationController::class, 'decline'])->name('moderation.tasks.decline');
        Route::post('/moderation/tasks/{task}/revision', [\App\Http\Controllers\AdminModerationController::class, 'requestRevision'])->name('moderation.tasks.revision');
        Route::post('/moderation/tasks/{task}/assign-self', [\App\Http\Controllers\AdminModerationController::class, 'assignSelf'])->name('moderation.tasks.assignSelf');
        Route::post('/moderation/tasks/{task}/assign', [\App\Http\Controllers\AdminModerationController::class, 'assignModerator'])->name('moderation.tasks.assign');
    });
});

Route::redirect('/super-admin', '/admin/dashboard');

// Stripe webhook moved to routes/api.php

Route::get('/business-for-sale', [BusinessSalesController::class, 'index'])->name('business.index');
Route::get('/business-for-sale/sample', [BusinessSalesController::class, 'sample'])->name('business.sample');
Route::get('/business-for-sale/{listing}', [BusinessSalesController::class, 'show'])->name('business.show');

// Business for Sale auth-only routes
Route::get('/business-for-sale/new', [BusinessSalesController::class, 'create'])->middleware(['auth'])->name('business.create');
Route::post('/business-for-sale', [BusinessSalesController::class, 'store'])->middleware(['auth'])->name('business.store');
Route::get('/business-for-sale/{listing}/edit', [BusinessSalesController::class, 'edit'])->middleware(['auth'])->name('business.edit');
Route::put('/business-for-sale/{listing}', [BusinessSalesController::class, 'update'])->middleware(['auth'])->name('business.update');
Route::post('/business-for-sale/{listing}/moderate', [BusinessSalesController::class, 'moderate'])->middleware(['auth'])->name('business.moderate');

// Stripe webhook moved to routes/api.php

// Service Company Dashboard routes
Route::middleware(['auth'])
    ->prefix('service-dashboard')
    ->name('service.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\ServiceDashboardController::class, 'index'])
            ->name('dashboard');

        // Services CRUD and publish
        Route::get('/services', [\App\Http\Controllers\ServiceController::class, 'index'])
            ->name('services.index');
        Route::get('/services/create', [\App\Http\Controllers\ServiceController::class, 'create'])
            ->name('services.create');
        Route::post('/services', [\App\Http\Controllers\ServiceController::class, 'store'])
            ->name('services.store');
        Route::get('/services/{service}/edit', [\App\Http\Controllers\ServiceController::class, 'edit'])
            ->name('services.edit');
        Route::put('/services/{service}', [\App\Http\Controllers\ServiceController::class, 'update'])
            ->name('services.update');
        Route::delete('/services/{service}', [\App\Http\Controllers\ServiceController::class, 'destroy'])
            ->name('services.destroy');
        Route::post('/services/{service}/publish', [\App\Http\Controllers\ServiceController::class, 'publish'])
            ->name('services.publish');

        // Incoming requests
        Route::get('/requests', [\App\Http\Controllers\ServiceRequestController::class, 'index'])
            ->name('requests.index');
        Route::post('/requests/{request}/accept', [\App\Http\Controllers\ServiceRequestController::class, 'accept'])
            ->name('requests.accept');
        Route::post('/requests/{request}/reject', [\App\Http\Controllers\ServiceRequestController::class, 'reject'])
            ->name('requests.reject');
        Route::post('/requests/{request}/reply', [\App\Http\Controllers\ServiceRequestController::class, 'reply'])
            ->name('requests.reply');

        // Cases
        Route::get('/cases', [\App\Http\Controllers\ServiceCaseController::class, 'index'])
            ->name('cases.index');
        Route::get('/cases/create', [\App\Http\Controllers\ServiceCaseController::class, 'create'])
            ->name('cases.create');
        Route::post('/cases', [\App\Http\Controllers\ServiceCaseController::class, 'store'])
            ->name('cases.store');
        Route::get('/cases/{case}/edit', [\App\Http\Controllers\ServiceCaseController::class, 'edit'])
            ->name('cases.edit');
        Route::put('/cases/{case}', [\App\Http\Controllers\ServiceCaseController::class, 'update'])
            ->name('cases.update');
        Route::delete('/cases/{case}', [\App\Http\Controllers\ServiceCaseController::class, 'destroy'])
            ->name('cases.destroy');

        // Documents
        Route::get('/documents', [\App\Http\Controllers\ServiceDocumentController::class, 'index'])
            ->name('documents.index');
        Route::post('/documents', [\App\Http\Controllers\ServiceDocumentController::class, 'store'])
            ->name('documents.store');
        Route::post('/documents/{document}/verify', [\App\Http\Controllers\ServiceDocumentController::class, 'verify'])
            ->name('documents.verify');
        Route::post('/documents/{document}/reject', [\App\Http\Controllers\ServiceDocumentController::class, 'reject'])
            ->name('documents.reject');

        // Reviews
        Route::get('/reviews', [\App\Http\Controllers\ServiceReviewController::class, 'index'])
            ->name('reviews.index');
        Route::post('/reviews/{review}/report', [\App\Http\Controllers\ServiceReviewController::class, 'report'])
            ->name('reviews.report');
        Route::post('/reviews/{review}/reply', [\App\Http\Controllers\ServiceReviewController::class, 'reply'])
            ->name('reviews.reply');

        // Activity
        Route::get('/activity', [\App\Http\Controllers\ServiceActivityController::class, 'index'])
            ->name('activity.index');

        // Company Profile
        Route::get('/profile', [\App\Http\Controllers\ServiceCompanyProfileController::class, 'show'])
            ->name('profile.show');
        Route::put('/profile', [\App\Http\Controllers\ServiceCompanyProfileController::class, 'update'])
            ->name('profile.update');

        // Drilling: Fleet (Rigs)
        Route::get('/drilling/rigs', [\App\Http\Controllers\DrillingRigController::class, 'index'])
            ->name('drilling.rigs.index');
        Route::post('/drilling/rigs', [\App\Http\Controllers\DrillingRigController::class, 'store'])
            ->name('drilling.rigs.store');
        Route::delete('/drilling/rigs/{rig}', [\App\Http\Controllers\DrillingRigController::class, 'destroy'])
            ->name('drilling.rigs.destroy');

        // Drilling: HSE Documents
        Route::get('/drilling/hse', [\App\Http\Controllers\DrillingHseDocumentController::class, 'index'])
            ->name('drilling.hse.index');
        Route::post('/drilling/hse', [\App\Http\Controllers\DrillingHseDocumentController::class, 'store'])
            ->name('drilling.hse.store');
        Route::post('/drilling/hse/{document}/verify', [\App\Http\Controllers\DrillingHseDocumentController::class, 'verify'])
            ->name('drilling.hse.verify');
        Route::delete('/drilling/hse/{document}', [\App\Http\Controllers\DrillingHseDocumentController::class, 'destroy'])
            ->name('drilling.hse.destroy');

        // Drilling: Cases (Portfolio)
        Route::get('/drilling/cases', [\App\Http\Controllers\DrillingCaseController::class, 'index'])
            ->name('drilling.cases.index');
        Route::post('/drilling/cases', [\App\Http\Controllers\DrillingCaseController::class, 'store'])
            ->name('drilling.cases.store');
        Route::put('/drilling/cases/{case}', [\App\Http\Controllers\DrillingCaseController::class, 'update'])
            ->name('drilling.cases.update');
        Route::delete('/drilling/cases/{case}', [\App\Http\Controllers\DrillingCaseController::class, 'destroy'])
            ->name('drilling.cases.destroy');

        // Logistics: Services management
        Route::get('/logistics/services', [\App\Http\Controllers\LogisticsServiceController::class, 'index'])
            ->name('logistics.services.index');
        Route::post('/logistics/services', [\App\Http\Controllers\LogisticsServiceController::class, 'store'])
            ->name('logistics.services.store');

        // Logistics: Routes management
        Route::get('/logistics/routes', [\App\Http\Controllers\LogisticsRouteController::class, 'index'])
            ->name('logistics.routes.index');
        Route::post('/logistics/routes', [\App\Http\Controllers\LogisticsRouteController::class, 'store'])
            ->name('logistics.routes.store');
        Route::put('/logistics/routes/{route}', [\App\Http\Controllers\LogisticsRouteController::class, 'update'])
            ->name('logistics.routes.update');
        Route::delete('/logistics/routes/{route}', [\App\Http\Controllers\LogisticsRouteController::class, 'destroy'])
            ->name('logistics.routes.destroy');
        Route::delete('/logistics/routes/{route}/documents/{index}', [\App\Http\Controllers\LogisticsRouteController::class, 'destroyDocument'])
            ->name('logistics.routes.documents.destroy');

        // Tenders: Customer management
        Route::get('/tenders', [\App\Http\Controllers\TenderController::class, 'index'])
            ->name('tenders.index');
        Route::post('/tenders', [\App\Http\Controllers\TenderController::class, 'store'])
            ->name('tenders.store');
        Route::put('/tenders/{tender}', [\App\Http\Controllers\TenderController::class, 'update'])
            ->name('tenders.update');
        Route::delete('/tenders/{tender}', [\App\Http\Controllers\TenderController::class, 'destroy'])
            ->name('tenders.destroy');
        Route::post('/tenders/{tender}/extend', [\App\Http\Controllers\TenderController::class, 'extend'])
            ->name('tenders.extend');
    });

// Public reviews submission
Route::post('/companies/{company}/reviews', [\App\Http\Controllers\ReviewPublicController::class, 'store'])
    ->name('companies.reviews.store');

Route::get('/drilling', [DrillingCatalogController::class, 'index'])->name('drilling.index');
Route::get('/drilling/companies/{company}', [DrillingCatalogController::class, 'show'])->name('drilling.company.show');

// Logistics public routes
Route::get('/logistics', [\App\Http\Controllers\LogisticsCatalogController::class, 'index'])->name('logistics.index');
Route::get('/logistics/companies/{company}', [\App\Http\Controllers\LogisticsCatalogController::class, 'show'])->name('logistics.company.show');

// Tenders public routes
Route::get('/tenders', [\App\Http\Controllers\TenderPublicController::class, 'index'])->name('tenders.public.index');
Route::get('/tenders/{tender}', [\App\Http\Controllers\TenderPublicController::class, 'show'])->name('tenders.public.show');
// Auth-only application submission
Route::post('/tenders/{tender}/apply', [\App\Http\Controllers\TenderApplicationController::class, 'store'])
    ->middleware(['auth'])
    ->name('tenders.apply');
 
 // Geo endpoints (public)
 Route::get('/geo/countries', [\App\Http\Controllers\GeoController::class, 'countries'])->name('geo.countries');
 Route::get('/geo/cities', [\App\Http\Controllers\GeoController::class, 'cities'])->name('geo.cities');
 
 // Stocks pages
 Route::get('/stocks', [\App\Http\Controllers\StocksController::class, 'index'])->name('stocks.index');
 Route::get('/stocks/buy', [\App\Http\Controllers\StocksController::class, 'buy'])->name('stocks.buy');
 Route::post('/stocks/buy', [\App\Http\Controllers\StocksController::class, 'storeBuy'])
     ->middleware(['auth'])
     ->name('stocks.buy.store');
 Route::get('/stocks/buy/cancel', [\App\Http\Controllers\StocksController::class, 'cancelBuy'])
     ->middleware(['auth'])
     ->name('stocks.buy.cancel');
 Route::get('/stocks/market', [\App\Http\Controllers\MarketController::class, 'index'])->name('stocks.market');
 Route::post('/stocks/market/offers', [\App\Http\Controllers\MarketController::class, 'storeOffer'])
     ->middleware(['auth'])
     ->name('stocks.market.offers.store');
 
 // Investor dashboard (auth)
 Route::middleware(['auth'])->group(function () {
     Route::get('/investor-dashboard', [\App\Http\Controllers\InvestorDashboardController::class, 'index'])->name('investor.dashboard');
     // KYC
     Route::get('/kyc', [\App\Http\Controllers\KycController::class, 'index'])->name('kyc.index');
     Route::post('/kyc', [\App\Http\Controllers\KycController::class, 'store'])->name('kyc.store');
 });
 
 // Payment webhooks (providers)
 Route::post('/payments/stripe/webhook', [\App\Http\Controllers\PaymentController::class, 'stripeWebhook'])->name('payments.stripe.webhook');
 Route::post('/payments/yookassa/webhook', [\App\Http\Controllers\PaymentController::class, 'yookassaWebhook'])->name('payments.yookassa.webhook');
 Route::post('/payments/crypto/webhook', [\App\Http\Controllers\PaymentController::class, 'cryptoWebhook'])->name('payments.crypto.webhook');
Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationsController::class, 'markAsRead'])->middleware(['auth'])->name('notifications.read');
// Add bulk mark-all endpoint
Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationsController::class, 'markAllAsRead'])->middleware(['auth'])->name('notifications.read_all');

// Include authentication-related routes (login, register, password, role selection, etc.)
require __DIR__ . '/auth.php';
        // Freelance services moderation
        Route::get('/freelance/services', [\App\Http\Controllers\AdminFreelanceController::class, 'index'])->name('freelance.services.index');
        Route::post('/freelance/services/{service}/approve', [\App\Http\Controllers\AdminFreelanceController::class, 'approve'])->name('freelance.services.approve');
        Route::post('/freelance/services/{service}/reject', [\App\Http\Controllers\AdminFreelanceController::class, 'reject'])->name('freelance.services.reject');
        Route::post('/freelance/services/{service}/feature', [\App\Http\Controllers\AdminFreelanceController::class, 'feature'])->name('freelance.services.feature');

        // Freelance categories & subcategories
        Route::get('/freelance/categories', [\App\Http\Controllers\AdminFreelanceCategoryController::class, 'index'])->name('freelance.categories.index');
        Route::post('/freelance/categories', [\App\Http\Controllers\AdminFreelanceCategoryController::class, 'storeCategory'])->name('freelance.categories.store');
        Route::delete('/freelance/categories/{category}', [\App\Http\Controllers\AdminFreelanceCategoryController::class, 'destroyCategory'])->name('freelance.categories.destroy');
        Route::post('/freelance/subcategories', [\App\Http\Controllers\AdminFreelanceCategoryController::class, 'storeSubcategory'])->name('freelance.subcategories.store');
        Route::delete('/freelance/subcategories/{subcategory}', [\App\Http\Controllers\AdminFreelanceCategoryController::class, 'destroySubcategory'])->name('freelance.subcategories.destroy');

        // Freelance reviews moderation
        Route::get('/freelance/reviews', [\App\Http\Controllers\AdminFreelanceReviewController::class, 'index'])->name('freelance.reviews.index');
        Route::post('/freelance/reviews/{review}/status', [\App\Http\Controllers\AdminFreelanceReviewController::class, 'setStatus'])->name('freelance.reviews.status');
        // KYC moderation
        Route::get('/kyc', [\App\Http\Controllers\AdminKycController::class, 'index'])->name('kyc.index');
        Route::post('/kyc/{kyc}/approve', [\App\Http\Controllers\AdminKycController::class, 'approve'])->name('kyc.approve');
        Route::post('/kyc/{kyc}/reject', [\App\Http\Controllers\AdminKycController::class, 'reject'])->name('kyc.reject');

        // Jobs moderation
        Route::get('/jobs', [\App\Http\Controllers\AdminJobsController::class, 'index'])->name('jobs.index');
        Route::post('/jobs/{job}/approve', [\App\Http\Controllers\AdminJobsController::class, 'approve'])->name('jobs.approve');
        Route::post('/jobs/{job}/reject', [\App\Http\Controllers\AdminJobsController::class, 'reject'])->name('jobs.reject');

        // Settings CMS
        Route::get('/settings', [\App\Http\Controllers\AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\AdminSettingsController::class, 'save'])->name('settings.save');

        // Withdrawals approval
        Route::get('/finance/withdrawals', [\App\Http\Controllers\AdminWithdrawalController::class, 'index'])->name('finance.withdrawals.index');
        Route::post('/finance/withdrawals/{paymentRequest}/approve', [\App\Http\Controllers\AdminWithdrawalController::class, 'approve'])->name('finance.withdrawals.approve');
        Route::post('/finance/withdrawals/{paymentRequest}/reject', [\App\Http\Controllers\AdminWithdrawalController::class, 'reject'])->name('finance.withdrawals.reject');

        // Platform fees
        Route::get('/finance/platform-fees', [\App\Http\Controllers\AdminPlatformFeeController::class, 'index'])->name('finance.platform_fees.index');
        Route::post('/finance/platform-fees/{fee}/collect', [\App\Http\Controllers\AdminPlatformFeeController::class, 'collect'])->name('finance.platform_fees.collect');

        // SMTP test
        Route::get('/mail/test', [\App\Http\Controllers\AdminMailController::class, 'index'])->name('mail.test');
        Route::post('/mail/test', [\App\Http\Controllers\AdminMailController::class, 'send'])->name('mail.test.send');
