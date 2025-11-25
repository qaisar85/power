<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LogisticsRoute;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogisticsCatalogController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->string('type')->toString(); // road, air, sea, rail, warehousing, customs
        $fromCountry = $request->string('from_country')->toString();
        $toCountry = $request->string('to_country')->toString();
        $cert = $request->string('cert')->toString(); // IATA, FIATA, ISO

        $companies = Company::query()
            ->active()->verified()
            ->with(['services' => function ($q) {
                $q->where('category', 'logistics');
            }])
            ->when($type, function ($q) use ($type) {
                $q->whereHas('routes', function ($qr) use ($type) {
                    $qr->where('transport_type', $type);
                });
            })
            ->when($fromCountry, function ($q) use ($fromCountry) {
                $q->whereHas('routes', function ($qr) use ($fromCountry) {
                    $qr->where('from_country', $fromCountry);
                });
            })
            ->when($toCountry, function ($q) use ($toCountry) {
                $q->whereHas('routes', function ($qr) use ($toCountry) {
                    $qr->where('to_country', $toCountry);
                });
            })
            ->when($cert, function ($q) use ($cert) {
                $q->whereHas('documents', function ($qd) use ($cert) {
                    $qd->where('type', $cert)->where('status', 'verified');
                });
            })
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Logistics/Index', [
            'companies' => $companies,
            'filters' => [
                'type' => $type,
                'from_country' => $fromCountry,
                'to_country' => $toCountry,
                'cert' => $cert,
            ],
        ]);
    }

    public function show(Company $company)
    {
        $company->load([
            'services' => function ($q) { $q->where('category', 'logistics'); },
            'documents' => function ($q) { $q->orderBy('expires_at'); },
        ]);

        $routes = LogisticsRoute::where('company_id', $company->id)
            ->orderBy('from_country')
            ->orderBy('to_country')
            ->get();

        // Determine contact visibility: paid subscription => open; commission => hidden
        $placementType = $company->services()->where('category','logistics')->value('placement_type') ?? 'free';
        $contactsOpen = $placementType === 'paid';

        return Inertia::render('Logistics/Company', [
            'company' => $company,
            'routes' => $routes,
            'contacts_open' => $contactsOpen,
        ]);
    }
}