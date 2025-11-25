<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\City;

class GeoController extends Controller
{
    public function countries(Request $request)
    {
        $countries = Country::query()
            ->select(['id', 'name', 'code'])
            ->orderBy('name')
            ->get();

        return response()->json($countries);
    }

    public function cities(Request $request)
    {
        $countryName = $request->query('country');
        if (!$countryName) {
            return response()->json([]);
        }

        $country = Country::where('name', $countryName)->first();
        if (!$country) {
            // Fallback: try by code if name wasn't matched
            $country = Country::where('code', $countryName)->first();
        }
        if (!$country) {
            return response()->json([]);
        }

        $cities = City::query()
            ->where('country_id', $country->id)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return response()->json($cities);
    }
}