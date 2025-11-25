<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessSector;
use App\Models\BusinessSubsector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class SectorController extends Controller
{
    public function index(Request $request)
    {
        $standard = trim((string) $request->query('standard', ''));
        $code = trim((string) $request->query('code', ''));
        $q = trim((string) $request->query('q', ''));
        $includeChildren = filter_var($request->query('include_children', 'true'), FILTER_VALIDATE_BOOLEAN);

        if ($code !== '') {
            return $this->respondFoundByCode($code);
        }

        $query = BusinessSector::query()
            ->when($standard !== '', fn($builder) => $builder->where('standard', $standard))
            // Fix lexical variable collision: do not reuse $q as closure parameter
            ->when($q !== '', function ($builder) use ($q) {
                $term = '%' . str_replace('%', '', $q) . '%';
                $builder->where(function ($inner) use ($term) {
                    $inner->where('name', 'LIKE', $term)
                         ->orWhere('code', 'LIKE', $term)
                         ->orWhere('slug', 'LIKE', $term);
                });
            })
            ->orderBy('sort_order');

        if ($includeChildren) {
            // Load children (level 2) instead of non-existent 'subsectors' relation
            $query->with(['children' => function ($q) {
                $q->orderBy('sort_order');
            }]);
        }

        $sectors = $query->get()->map(fn($s) => $this->formatSector($s, $includeChildren))->values();

        return response()->json([
            'data' => $sectors,
        ]);
    }

    public function show(Request $request, string $code)
    {
        return $this->respondFoundByCode($code);
    }

    protected function respondFoundByCode(string $code)
    {
        $sector = BusinessSector::where('code', $code)->first();
        if ($sector) {
            // Ensure we load children relation (level 2)
            $sector->load(['children' => function ($q) { $q->orderBy('sort_order'); }]);
            return response()->json(['data' => $this->formatSector($sector, true)]);
        }

        $sub = BusinessSubsector::where('code', $code)->with('sector')->first();
        if ($sub) {
            return response()->json([
                'data' => [
                    'type' => 'subsector',
                    'standard' => $sub->standard ?? $sub->sector->standard ?? null,
                    'code' => $sub->code,
                    'name' => $this->labelFor($sub->name, $sub->code, $sub->slug),
                    'slug' => $sub->slug,
                    'sector' => $this->formatSector($sub->sector, false),
                ],
            ]);
        }

        return response()->json(['error' => 'Code not found'], 404);
    }

    protected function formatSector(BusinessSector $sector, bool $includeChildren = true): array
    {
        $data = [
            'type' => 'sector',
            'standard' => $sector->standard,
            'code' => $sector->code,
            'name' => $this->labelFor($sector->name, $sector->code, $sector->slug),
            'slug' => $sector->slug,
            'icon' => $sector->icon,
        ];

        if ($includeChildren) {
            $data['children'] = $sector->children->map(function ($c) use ($sector) {
                return [
                    'standard' => $c->standard ?? $sector->standard,
                    'code' => $c->code,
                    'name' => $this->labelFor($c->name, $c->code, $c->slug),
                    'slug' => $c->slug,
                ];
            })->values();
        }

        return $data;
    }

    protected function labelFor(string $name, ?string $code, ?string $slug): string
    {
        $key = $code ?: $slug;
        if ($key && Lang::has('sectors.' . $key)) {
            return __('sectors.' . $key);
        }
        return $name;
    }
}