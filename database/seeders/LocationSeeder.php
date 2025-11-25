<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            // United States
            [
                'name' => 'United States',
                'code' => 'US',
                'type' => 'country',
                'currency' => 'USD',
                'timezone' => 'America/New_York',
                'states' => [
                    [
                        'name' => 'California',
                        'code' => 'CA',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'Los Angeles', 'type' => 'city', 'latitude' => 34.0522, 'longitude' => -118.2437],
                            ['name' => 'San Francisco', 'type' => 'city', 'latitude' => 37.7749, 'longitude' => -122.4194],
                            ['name' => 'San Diego', 'type' => 'city', 'latitude' => 32.7157, 'longitude' => -117.1611],
                        ]
                    ],
                    [
                        'name' => 'New York',
                        'code' => 'NY',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'New York City', 'type' => 'city', 'latitude' => 40.7128, 'longitude' => -74.0060],
                            ['name' => 'Buffalo', 'type' => 'city', 'latitude' => 42.8864, 'longitude' => -78.8784],
                        ]
                    ],
                    [
                        'name' => 'Texas',
                        'code' => 'TX',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'Houston', 'type' => 'city', 'latitude' => 29.7604, 'longitude' => -95.3698],
                            ['name' => 'Dallas', 'type' => 'city', 'latitude' => 32.7767, 'longitude' => -96.7970],
                            ['name' => 'Austin', 'type' => 'city', 'latitude' => 30.2672, 'longitude' => -97.7431],
                        ]
                    ],
                ]
            ],
            // United Kingdom
            [
                'name' => 'United Kingdom',
                'code' => 'GB',
                'type' => 'country',
                'currency' => 'GBP',
                'timezone' => 'Europe/London',
                'states' => [
                    [
                        'name' => 'England',
                        'code' => 'ENG',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'London', 'type' => 'city', 'latitude' => 51.5074, 'longitude' => -0.1278],
                            ['name' => 'Manchester', 'type' => 'city', 'latitude' => 53.4808, 'longitude' => -2.2426],
                            ['name' => 'Birmingham', 'type' => 'city', 'latitude' => 52.4862, 'longitude' => -1.8904],
                        ]
                    ],
                    [
                        'name' => 'Scotland',
                        'code' => 'SCT',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'Edinburgh', 'type' => 'city', 'latitude' => 55.9533, 'longitude' => -3.1883],
                            ['name' => 'Glasgow', 'type' => 'city', 'latitude' => 55.8642, 'longitude' => -4.2518],
                        ]
                    ],
                ]
            ],
            // India
            [
                'name' => 'India',
                'code' => 'IN',
                'type' => 'country',
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'states' => [
                    [
                        'name' => 'Maharashtra',
                        'code' => 'MH',
                        'type' => 'state',
                        'cities' => [
                            [
                                'name' => 'Mumbai',
                                'type' => 'city',
                                'latitude' => 19.0760,
                                'longitude' => 72.8777,
                                'districts' => [
                                    [
                                        'name' => 'Mumbai City',
                                        'type' => 'district',
                                        'villages' => [
                                            ['name' => 'Colaba', 'type' => 'village'],
                                            ['name' => 'Fort', 'type' => 'village'],
                                        ]
                                    ],
                                    [
                                        'name' => 'Mumbai Suburban',
                                        'type' => 'district',
                                        'villages' => [
                                            ['name' => 'Andheri', 'type' => 'village'],
                                            ['name' => 'Bandra', 'type' => 'village'],
                                        ]
                                    ],
                                ]
                            ],
                            ['name' => 'Pune', 'type' => 'city', 'latitude' => 18.5204, 'longitude' => 73.8567],
                        ]
                    ],
                    [
                        'name' => 'Delhi',
                        'code' => 'DL',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'New Delhi', 'type' => 'city', 'latitude' => 28.6139, 'longitude' => 77.2090],
                        ]
                    ],
                ]
            ],
            // UAE
            [
                'name' => 'United Arab Emirates',
                'code' => 'AE',
                'type' => 'country',
                'currency' => 'AED',
                'timezone' => 'Asia/Dubai',
                'states' => [
                    [
                        'name' => 'Dubai',
                        'code' => 'DU',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'Dubai City', 'type' => 'city', 'latitude' => 25.2048, 'longitude' => 55.2708],
                        ]
                    ],
                    [
                        'name' => 'Abu Dhabi',
                        'code' => 'AZ',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'Abu Dhabi City', 'type' => 'city', 'latitude' => 24.4539, 'longitude' => 54.3773],
                        ]
                    ],
                ]
            ],
            // Canada
            [
                'name' => 'Canada',
                'code' => 'CA',
                'type' => 'country',
                'currency' => 'CAD',
                'timezone' => 'America/Toronto',
                'states' => [
                    [
                        'name' => 'Ontario',
                        'code' => 'ON',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'Toronto', 'type' => 'city', 'latitude' => 43.6532, 'longitude' => -79.3832],
                            ['name' => 'Ottawa', 'type' => 'city', 'latitude' => 45.4215, 'longitude' => -75.6972],
                        ]
                    ],
                    [
                        'name' => 'British Columbia',
                        'code' => 'BC',
                        'type' => 'state',
                        'cities' => [
                            ['name' => 'Vancouver', 'type' => 'city', 'latitude' => 49.2827, 'longitude' => -123.1207],
                        ]
                    ],
                ]
            ],
        ];

        $this->createLocations($locations);
    }

    private function createLocations($locations, $parentId = null)
    {
        foreach ($locations as $locationData) {
            $states = $locationData['states'] ?? [];
            $cities = $locationData['cities'] ?? [];
            $districts = $locationData['districts'] ?? [];
            $villages = $locationData['villages'] ?? [];
            
            unset($locationData['states'], $locationData['cities'], $locationData['districts'], $locationData['villages']);
            
            $locationData['parent_id'] = $parentId;
            $location = Location::create($locationData);
            
            if (!empty($states)) {
                $this->createLocations($states, $location->id);
            }
            if (!empty($cities)) {
                $this->createLocations($cities, $location->id);
            }
            if (!empty($districts)) {
                $this->createLocations($districts, $location->id);
            }
            if (!empty($villages)) {
                $this->createLocations($villages, $location->id);
            }
        }
    }
}