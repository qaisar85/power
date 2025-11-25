<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class CountryStateCitySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('cities')->truncate();
        DB::table('states')->truncate();
        DB::table('countries')->truncate();
        Schema::enableForeignKeyConstraints();

        $path = database_path('seeders/data/locations.json');
        if (!file_exists($path)) {
            $this->command?->warn('locations.json not found; skipping seeding');
            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            $this->command?->warn('locations.json invalid; skipping seeding');
            return;
        }

        foreach ($data as $country) {
            $countryModel = Country::create([
                'name' => $country['name'],
                'slug' => Str::slug($country['name']),
                'iso2' => $country['iso2'] ?? null,
                'iso3' => $country['iso3'] ?? null,
                'phone_code' => $country['phone_code'] ?? null,
                'region' => $country['region'] ?? null,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            foreach ($country['states'] ?? [] as $state) {
                $stateModel = State::create([
                    'country_id' => $countryModel->id,
                    'name' => $state['name'],
                    'slug' => Str::slug($state['name']),
                    'code' => $state['code'] ?? null,
                    'is_active' => true,
                    'sort_order' => 0,
                ]);

                foreach ($state['cities'] ?? [] as $cityName) {
                    City::create([
                        'country_id' => $countryModel->id,
                        'state_id' => $stateModel->id,
                        'name' => $cityName,
                        'slug' => Str::slug($cityName),
                        'is_active' => true,
                        'sort_order' => 0,
                    ]);
                }
            }
        }
    }
}