<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ListingController;
use App\Http\Controllers\Api\V1\PackageController;
use App\Http\Controllers\Api\V1\WalletController;
use App\Http\Controllers\Api\V1\Admin\PaymentMethodController as AdminPaymentMethodController;
use App\Http\Controllers\Api\V1\AgentController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\TenderController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\AuctionController;
use App\Http\Controllers\Api\V1\ReferralController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BusinessSectorController;
use App\Http\Controllers\Api\V1\FormController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FreelanceOrderController;
use App\Http\Controllers\Api\V1\FreelanceReviewController;
use App\Http\Controllers\Api\V1\AccountController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Mobile application API endpoints with versioning
|
*/

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Listings Management
    Route::prefix('listings')->group(function () {
        Route::get('/', [ListingController::class, 'index']);
        Route::get('/{listing}', [ListingController::class, 'show']);
        Route::post('/', [ListingController::class, 'store']);
        Route::put('/{listing}', [ListingController::class, 'update']);
        Route::delete('/{listing}', [ListingController::class, 'destroy']);
        Route::post('/{listing}/publish', [ListingController::class, 'publish']);

        // Listing actions
        Route::post('/{listing}/request-info', [ListingController::class, 'requestInfo']);
        Route::post('/{listing}/report', [ListingController::class, 'report']);
    });

    // Search & Filters
    Route::prefix('search')->group(function () {
        Route::get('/listings', [SearchController::class, 'listings']);
        Route::get('/companies', [SearchController::class, 'companies']);
        Route::get('/agents', [SearchController::class, 'agents']);
        Route::get('/filters', [SearchController::class, 'availableFilters']);
        Route::get('/suggestions', [SearchController::class, 'suggestions']);
    });

    // Packages & Subscriptions
    Route::prefix('packages')->group(function () {
        Route::get('/', [PackageController::class, 'index']);
        Route::get('/{package}', [PackageController::class, 'show']);
        Route::post('/subscribe', [PackageController::class, 'subscribe']);
        Route::get('/my-subscriptions', [PackageController::class, 'mySubscriptions']);
        Route::post('/cancel/{subscription}', [PackageController::class, 'cancel']);
    });

    // Wallet Management
    Route::prefix('wallet')->group(function () {
        Route::get('/balance', [WalletController::class, 'balance']);
        Route::get('/transactions', [WalletController::class, 'transactions']);
        Route::post('/topup', [WalletController::class, 'topup']);
        Route::post('/transfer', [WalletController::class, 'transfer']);
        Route::post('/withdraw', [WalletController::class, 'withdraw']);
        Route::get('/payment-methods', [WalletController::class, 'paymentMethods']);
    });

    // Admin payment methods management
    Route::prefix('admin')->group(function () {
        // List and manage payment methods (basic checks in controller enforce admin-only actions)
        Route::get('/payment-methods', [AdminPaymentMethodController::class, 'index']);
        Route::patch('/payment-methods/{id}', [AdminPaymentMethodController::class, 'update']);
    });

    // Regional Agents
    Route::prefix('agents')->group(function () {
        Route::get('/', [AgentController::class, 'index']);
        Route::get('/{agent}', [AgentController::class, 'show']);
        Route::post('/{agent}/request-service', [AgentController::class, 'requestService']);
        Route::get('/{agent}/reviews', [AgentController::class, 'reviews']);
        Route::post('/{agent}/review', [AgentController::class, 'submitReview']);

        // Agent dashboard (for agents only)
        Route::middleware(['agent'])->group(function () {
            Route::get('/dashboard/stats', [AgentController::class, 'dashboardStats']);
            Route::get('/dashboard/services', [AgentController::class, 'myServices']);
            Route::post('/services/{service}/accept', [AgentController::class, 'acceptService']);
            Route::post('/services/{service}/complete', [AgentController::class, 'completeService']);
        });
    });

    // Favorites
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/{listing}', [FavoriteController::class, 'store']);
        Route::delete('/{listing}', [FavoriteController::class, 'destroy']);
        Route::get('/check/{listing}', [FavoriteController::class, 'check']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Company Management
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/{company}', [CompanyController::class, 'show']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::put('/{company}', [CompanyController::class, 'update']);
        Route::delete('/{company}', [CompanyController::class, 'destroy']);
        Route::post('/{company}/verify', [CompanyController::class, 'requestVerification']);
    });

    // Tenders
    Route::prefix('tenders')->group(function () {
        Route::get('/', [TenderController::class, 'index']);
        Route::get('/{tender}', [TenderController::class, 'show']);
        Route::post('/', [TenderController::class, 'store']);
        Route::post('/{tender}/apply', [TenderController::class, 'apply']);
        Route::get('/my-applications', [TenderController::class, 'myApplications']);
    });

    // Jobs
    Route::prefix('jobs')->group(function () {
        Route::get('/', [JobController::class, 'index']);
        Route::get('/{job}', [JobController::class, 'show']);
        Route::post('/', [JobController::class, 'store']);
        Route::post('/{job}/apply', [JobController::class, 'apply'])->middleware('recaptcha');
        Route::get('/my-applications', [JobController::class, 'myApplications']);
    });

    // Auctions
    Route::prefix('auctions')->group(function () {
        Route::get('/', [AuctionController::class, 'index']);
        Route::get('/{listing}', [AuctionController::class, 'show']);
        Route::post('/{listing}/bid', [AuctionController::class, 'placeBid']);
        Route::get('/{listing}/bids', [AuctionController::class, 'listBids']);
        Route::get('/my-bids', [AuctionController::class, 'myBids']);
    });

    // Freelance Orders
    Route::prefix('freelance/orders')->group(function () {
        Route::get('/', [FreelanceOrderController::class, 'index']);
        Route::post('/service/{service}', [FreelanceOrderController::class, 'store']);
        Route::post('/{order}/refund', [FreelanceOrderController::class, 'refund']);
    });

    // Freelance Service Reviews
    Route::prefix('freelance/services')->group(function () {
        Route::get('/{service}/reviews', [FreelanceReviewController::class, 'index']);
        Route::post('/{service}/review', [FreelanceReviewController::class, 'store'])->middleware('recaptcha');
        Route::get('/{service}/rating', [FreelanceReviewController::class, 'rating']);
    });

    // Chart of Accounts
    Route::prefix('accounts')->group(function () {
        Route::get('/dropdown', [AccountController::class, 'dropdown']);
        Route::post('/root/id-type', [AccountController::class, 'setRootIdType']);
        Route::post('/{parent}/segment', [AccountController::class, 'createSegment']);
        Route::post('/{parent}/leaf', [AccountController::class, 'createLeaf']);
    });

    // User Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', function (\Illuminate\Http\Request $request) {
            return response()->json($request->user()->load(['companies', 'wallet']));
        });
        Route::put('/', function (\Illuminate\Http\Request $request) {
            $request->user()->update($request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'country' => 'sometimes|string|max:100',
                'timezone' => 'sometimes|string|max:50',
            ]));
            return response()->json($request->user());
        });
    });

    // Referral System
    Route::prefix('referrals')->group(function () {
        Route::get('/stats', [ReferralController::class, 'stats']);
        Route::get('/tree', [ReferralController::class, 'tree']);
        Route::post('/generate-code', [ReferralController::class, 'generateCode']);
    });

    // Analytics (user-specific)
    Route::get('/analytics', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        return response()->json([
            'listings_count' => \App\Models\Listing::where('user_id', $user->id)->count(),
            'active_listings' => \App\Models\Listing::where('user_id', $user->id)->where('status', 'published')->count(),
            'total_views' => 0, // Implement view tracking
            'favorites_count' => \App\Models\Favorite::where('user_id', $user->id)->count(),
        ]);
    });
});

// Public endpoints (no auth required)
Route::prefix('v1/public')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/listings', [ListingController::class, 'publicIndex']);
    Route::get('/listings/{listing}', [ListingController::class, 'publicShow']);
    Route::get('/categories', [SearchController::class, 'categories']);
    Route::get('/countries', [SearchController::class, 'countries']);
    Route::get('/sectors', [BusinessSectorController::class, 'sectors']);
    Route::get('/sectors/{sector}/sub-sectors', [BusinessSectorController::class, 'subSectors']);
    Route::get('/sub-sectors/{subSector}/sub-sub-sectors', [BusinessSectorController::class, 'subSubSectors']);
    Route::get('/sectors/hierarchy', [BusinessSectorController::class, 'hierarchy']);
    Route::get('/sectors/search', [BusinessSectorController::class, 'search']);
    Route::get('/forms/company-registration', [FormController::class, 'companyRegistrationForm']);
    Route::get('/forms/job-application', [FormController::class, 'jobApplicationForm']);
    Route::get('/agents', [AgentController::class, 'publicIndex']);
    
    // Location endpoints
    Route::get('/locations/countries', [LocationController::class, 'countries']);
    Route::get('/locations/countries/{country}/states', [LocationController::class, 'states']);
    Route::get('/locations/states/{state}/cities', [LocationController::class, 'cities']);
    Route::get('/locations/cities/{city}/districts', [LocationController::class, 'districts']);
    Route::get('/locations/districts/{district}/villages', [LocationController::class, 'villages']);
    Route::get('/locations/search', [LocationController::class, 'search']);
    
    // Category endpoints
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/priority/{priority}', [CategoryController::class, 'byPriority']);
    Route::get('/categories/priority-1', [CategoryController::class, 'priority1']);
    Route::get('/categories/priority-2', [CategoryController::class, 'priority2']);
    Route::get('/categories/grouped', [CategoryController::class, 'grouped']);
});
