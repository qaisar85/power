<?php

namespace App\Http\Controllers;

use App\Models\DrillingCompany;
use App\Models\DrillingCase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DrillingCatalogController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'type' => $request->string('type')->toString(),
            'method' => $request->string('method')->toString(),
            'region' => $request->string('region')->toString(),
            'certificates' => $request->has('certificates') ? $request->input('certificates') : null,
            'depth' => $request->has('depth') ? (int) $request->input('depth') : null,
        ];

        $query = DrillingCompany::query()
            ->with(['services' => function ($q) {
                $q->select('id', 'company_id', 'type', 'method', 'depth', 'region', 'certificates');
            }])
            ->when($filters['region'], function ($q) use ($filters) {
                $q->where('region', 'like', '%' . $filters['region'] . '%');
            })
            ->when($filters['type'] || $filters['method'] || $filters['depth'] || $filters['certificates'] || $filters['region'], function ($q) use ($filters) {
                $q->whereHas('services', function ($s) use ($filters) {
                    if ($filters['type']) {
                        $s->where('type', 'like', '%' . $filters['type'] . '%');
                    }
                    if ($filters['method']) {
                        $s->where('method', 'like', '%' . $filters['method'] . '%');
                    }
                    if ($filters['region']) {
                        $s->where('region', 'like', '%' . $filters['region'] . '%');
                    }
                    if (!is_null($filters['depth'])) {
                        $s->where('depth', '>=', $filters['depth']);
                    }
                    if ($filters['certificates']) {
                        $certs = is_array($filters['certificates']) ? $filters['certificates'] : [ $filters['certificates'] ];
                        foreach ($certs as $c) {
                            if (is_string($c) && strlen(trim($c)) > 0) {
                                $s->whereJsonContains('certificates', trim($c));
                            }
                        }
                    }
                });
            });

        $companies = $query->orderBy('verified', 'desc')->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Drilling/Index', [
            'companies' => $companies,
            'filters' => [
                'type' => $filters['type'],
                'method' => $filters['method'],
                'region' => $filters['region'],
                'certificates' => $filters['certificates'],
                'depth' => $filters['depth'],
            ],
        ]);
    }

    public function show(DrillingCompany $company)
    {
        $cases = DrillingCase::where('company_id', $company->id)
            ->orderByDesc('start_date')
            ->orderBy('title')
            ->limit(12)
            ->get(['id','title','client','region','method','depth','start_date','end_date','status','tags','photos']);

        return Inertia::render('Drilling/Company', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'region' => $company->region,
                'verified' => (bool) $company->verified,
            ],
            'cases' => $cases,
        ]);
    }
}