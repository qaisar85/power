<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Listing;
use App\Models\BusinessSector;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModuleController extends Controller
{
    public function show(Request $request, string $slug = null)
    {
        // Resolve slug from route params when not provided
        $slug = $slug ?? $request->route('slug') ?? $request->route('module');

        $module = Module::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user = auth()->user();

        if ($module->requires_auth && $user && !$user->hasAccessToModule($slug)) {
            abort(403, 'Access denied to this module');
        }

        // Update last accessed timestamp
        if ($user) {
            $user->modules()->updateExistingPivot($module->id, [
                'last_accessed_at' => now()
            ]);
        }

        if ($module->integration_type === 'iframe') {
            return Inertia::render('ModuleIframe', [
                'module' => $module,
                'iframeUrl' => $module->config['iframe_url'] ?? null,
            ]);
        }

        // Marketplace: wire filters and provide categories/countries
        if ($slug === 'marketplace') {
            $filters = [
                'q' => trim((string) $request->query('q', '')),
                'category' => trim((string) $request->query('category', '')),
                'country' => trim((string) $request->query('country', '')),
                'type' => trim((string) $request->query('type', 'product')), // product|service
                'min_price' => $request->query('min_price'),
                'max_price' => $request->query('max_price'),
                'sort' => trim((string) $request->query('sort', 'latest')), // latest|price_asc|price_desc
            ];

            $query = Listing::query()
                ->where('status', 'published')
                ->when($filters['type'], fn($q, $t) => $q->where('type', $t))
                ->when($filters['q'], function ($q) use ($filters) {
                    $term = '%' . str_replace('%', '', strtolower($filters['q'])) . '%';
                    $q->where(function ($inner) use ($term) {
                        $inner->whereRaw('LOWER(title) LIKE ?', [$term])
                              ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
                    });
                })
                ->when($filters['category'], function ($q) use ($filters) {
                    $cat = strtolower($filters['category']);
                    $q->where(function ($inner) use ($cat) {
                        $inner->whereRaw('LOWER(category) = ?', [$cat])
                              ->orWhere('category', $filters['category']);
                    });
                })
                ->when($filters['country'], function ($q) use ($filters) {
                    $country = $filters['country'];
                    $q->where('location', 'LIKE', '%' . $country . '%');
                })
                ->when(!is_null($filters['min_price']), fn($q) => $q->where('price', '>=', (float) $filters['min_price']))
                ->when(!is_null($filters['max_price']), fn($q) => $q->where('price', '<=', (float) $filters['max_price']));

            // Sorting
            switch ($filters['sort']) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->orderByDesc('created_at');
            }

            $results = $query->paginate(12)->withQueryString()->through(function ($l) {
                return [
                    'id' => $l->id,
                    'title' => $l->title,
                    'location' => $l->location,
                    'category' => $l->category,
                    'price' => $l->price,
                    'currency' => $l->currency ?? 'USD',
                    'photo' => ($l->photos[0] ?? null),
                ];
            });

            // Categories from Business Sectors/Subsectors
            $categories = BusinessSector::active()
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

            // Countries derived from distinct locations (best-effort)
            $locations = Listing::query()
                ->select('location')
                ->whereNotNull('location')
                ->distinct()
                ->limit(500)
                ->pluck('location')
                ->toArray();

            $countries = collect($locations)
                ->map(function ($loc) {
                    $parts = array_map('trim', explode(',', (string) $loc));
                    return count($parts) ? trim(end($parts)) : trim((string) $loc);
                })
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();

            return Inertia::render('Modules/Marketplace', [
                'module' => $module,
                'config' => $module->config,
                'filters' => $filters,
                'results' => $results,
                'categories' => $categories,
                'countries' => $countries,
            ]);
        }

        // Native integration - route to specific module component
        return Inertia::render("Modules/{$module->name}", [
            'module' => $module,
            'config' => $module->config,
        ]);
    }
}