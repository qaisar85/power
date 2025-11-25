<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessSector;
use Illuminate\Support\Str;

class BusinessSectorSeeder extends Seeder
{
    public function run()
    {
        $sectors = [
            // Technology Sector
            [
                'name' => 'Technology',
                'code' => 'TECH',
                'level' => 1,
                'sort_order' => 1,
                'children' => [
                    [
                        'name' => 'Software Development',
                        'code' => 'TECH-SW',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Web Development', 'code' => 'TECH-SW-WEB', 'level' => 3],
                            ['name' => 'Mobile App Development', 'code' => 'TECH-SW-MOB', 'level' => 3],
                            ['name' => 'Enterprise Software', 'code' => 'TECH-SW-ENT', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Hardware & Electronics',
                        'code' => 'TECH-HW',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Computer Hardware', 'code' => 'TECH-HW-COMP', 'level' => 3],
                            ['name' => 'Consumer Electronics', 'code' => 'TECH-HW-CONS', 'level' => 3],
                            ['name' => 'Semiconductors', 'code' => 'TECH-HW-SEMI', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Telecommunications',
                        'code' => 'TECH-TEL',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Mobile Networks', 'code' => 'TECH-TEL-MOB', 'level' => 3],
                            ['name' => 'Internet Services', 'code' => 'TECH-TEL-INT', 'level' => 3],
                            ['name' => 'Satellite Communications', 'code' => 'TECH-TEL-SAT', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Financial Services
            [
                'name' => 'Financial Services',
                'code' => 'FIN',
                'level' => 1,
                'sort_order' => 2,
                'children' => [
                    [
                        'name' => 'Banking',
                        'code' => 'FIN-BANK',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Commercial Banking', 'code' => 'FIN-BANK-COM', 'level' => 3],
                            ['name' => 'Investment Banking', 'code' => 'FIN-BANK-INV', 'level' => 3],
                            ['name' => 'Digital Banking', 'code' => 'FIN-BANK-DIG', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Insurance',
                        'code' => 'FIN-INS',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Life Insurance', 'code' => 'FIN-INS-LIFE', 'level' => 3],
                            ['name' => 'Property Insurance', 'code' => 'FIN-INS-PROP', 'level' => 3],
                            ['name' => 'Health Insurance', 'code' => 'FIN-INS-HEAL', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Investment Services',
                        'code' => 'FIN-INV',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Asset Management', 'code' => 'FIN-INV-ASS', 'level' => 3],
                            ['name' => 'Wealth Management', 'code' => 'FIN-INV-WEA', 'level' => 3],
                            ['name' => 'Cryptocurrency', 'code' => 'FIN-INV-CRY', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Healthcare
            [
                'name' => 'Healthcare',
                'code' => 'HEAL',
                'level' => 1,
                'sort_order' => 3,
                'children' => [
                    [
                        'name' => 'Medical Services',
                        'code' => 'HEAL-MED',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Hospitals', 'code' => 'HEAL-MED-HOS', 'level' => 3],
                            ['name' => 'Clinics', 'code' => 'HEAL-MED-CLI', 'level' => 3],
                            ['name' => 'Telemedicine', 'code' => 'HEAL-MED-TEL', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Pharmaceuticals',
                        'code' => 'HEAL-PHA',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Drug Manufacturing', 'code' => 'HEAL-PHA-MAN', 'level' => 3],
                            ['name' => 'Biotechnology', 'code' => 'HEAL-PHA-BIO', 'level' => 3],
                            ['name' => 'Medical Devices', 'code' => 'HEAL-PHA-DEV', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Manufacturing
            [
                'name' => 'Manufacturing',
                'code' => 'MAN',
                'level' => 1,
                'sort_order' => 4,
                'children' => [
                    [
                        'name' => 'Automotive',
                        'code' => 'MAN-AUTO',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Vehicle Manufacturing', 'code' => 'MAN-AUTO-VEH', 'level' => 3],
                            ['name' => 'Auto Parts', 'code' => 'MAN-AUTO-PAR', 'level' => 3],
                            ['name' => 'Electric Vehicles', 'code' => 'MAN-AUTO-ELE', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Textiles & Apparel',
                        'code' => 'MAN-TEX',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Clothing Manufacturing', 'code' => 'MAN-TEX-CLO', 'level' => 3],
                            ['name' => 'Textile Production', 'code' => 'MAN-TEX-PRO', 'level' => 3],
                            ['name' => 'Fashion Design', 'code' => 'MAN-TEX-FAS', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Energy
            [
                'name' => 'Energy',
                'code' => 'ENE',
                'level' => 1,
                'sort_order' => 5,
                'children' => [
                    [
                        'name' => 'Renewable Energy',
                        'code' => 'ENE-REN',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Solar Energy', 'code' => 'ENE-REN-SOL', 'level' => 3],
                            ['name' => 'Wind Energy', 'code' => 'ENE-REN-WIN', 'level' => 3],
                            ['name' => 'Hydroelectric', 'code' => 'ENE-REN-HYD', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Oil & Gas',
                        'code' => 'ENE-OIL',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Exploration', 'code' => 'ENE-OIL-EXP', 'level' => 3],
                            ['name' => 'Refining', 'code' => 'ENE-OIL-REF', 'level' => 3],
                            ['name' => 'Distribution', 'code' => 'ENE-OIL-DIS', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Real Estate
            [
                'name' => 'Real Estate',
                'code' => 'REAL',
                'level' => 1,
                'sort_order' => 6,
                'children' => [
                    [
                        'name' => 'Residential',
                        'code' => 'REAL-RES',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Home Sales', 'code' => 'REAL-RES-SAL', 'level' => 3],
                            ['name' => 'Property Management', 'code' => 'REAL-RES-MAN', 'level' => 3],
                            ['name' => 'Real Estate Development', 'code' => 'REAL-RES-DEV', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Commercial',
                        'code' => 'REAL-COM',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Office Buildings', 'code' => 'REAL-COM-OFF', 'level' => 3],
                            ['name' => 'Retail Spaces', 'code' => 'REAL-COM-RET', 'level' => 3],
                            ['name' => 'Industrial Properties', 'code' => 'REAL-COM-IND', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Education
            [
                'name' => 'Education',
                'code' => 'EDU',
                'level' => 1,
                'sort_order' => 7,
                'children' => [
                    [
                        'name' => 'Higher Education',
                        'code' => 'EDU-HIGH',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Universities', 'code' => 'EDU-HIGH-UNI', 'level' => 3],
                            ['name' => 'Colleges', 'code' => 'EDU-HIGH-COL', 'level' => 3],
                            ['name' => 'Online Learning', 'code' => 'EDU-HIGH-ONL', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'K-12 Education',
                        'code' => 'EDU-K12',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Primary Schools', 'code' => 'EDU-K12-PRI', 'level' => 3],
                            ['name' => 'Secondary Schools', 'code' => 'EDU-K12-SEC', 'level' => 3],
                            ['name' => 'Private Schools', 'code' => 'EDU-K12-PRIV', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Retail & E-commerce
            [
                'name' => 'Retail & E-commerce',
                'code' => 'RET',
                'level' => 1,
                'sort_order' => 8,
                'children' => [
                    [
                        'name' => 'E-commerce',
                        'code' => 'RET-ECOM',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Online Marketplaces', 'code' => 'RET-ECOM-MAR', 'level' => 3],
                            ['name' => 'Direct-to-Consumer', 'code' => 'RET-ECOM-D2C', 'level' => 3],
                            ['name' => 'Digital Products', 'code' => 'RET-ECOM-DIG', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Traditional Retail',
                        'code' => 'RET-TRAD',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Department Stores', 'code' => 'RET-TRAD-DEP', 'level' => 3],
                            ['name' => 'Specialty Stores', 'code' => 'RET-TRAD-SPE', 'level' => 3],
                            ['name' => 'Grocery Stores', 'code' => 'RET-TRAD-GRO', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Transportation & Logistics
            [
                'name' => 'Transportation & Logistics',
                'code' => 'TRANS',
                'level' => 1,
                'sort_order' => 9,
                'children' => [
                    [
                        'name' => 'Shipping & Freight',
                        'code' => 'TRANS-SHIP',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Ocean Freight', 'code' => 'TRANS-SHIP-OCE', 'level' => 3],
                            ['name' => 'Air Freight', 'code' => 'TRANS-SHIP-AIR', 'level' => 3],
                            ['name' => 'Ground Transportation', 'code' => 'TRANS-SHIP-GRO', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Passenger Transportation',
                        'code' => 'TRANS-PASS',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Airlines', 'code' => 'TRANS-PASS-AIR', 'level' => 3],
                            ['name' => 'Public Transit', 'code' => 'TRANS-PASS-PUB', 'level' => 3],
                            ['name' => 'Ride Sharing', 'code' => 'TRANS-PASS-RID', 'level' => 3],
                        ]
                    ],
                ]
            ],
            // Agriculture & Food
            [
                'name' => 'Agriculture & Food',
                'code' => 'AGRI',
                'level' => 1,
                'sort_order' => 10,
                'children' => [
                    [
                        'name' => 'Food Production',
                        'code' => 'AGRI-FOOD',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Crop Production', 'code' => 'AGRI-FOOD-CRO', 'level' => 3],
                            ['name' => 'Livestock', 'code' => 'AGRI-FOOD-LIV', 'level' => 3],
                            ['name' => 'Food Processing', 'code' => 'AGRI-FOOD-PRO', 'level' => 3],
                        ]
                    ],
                    [
                        'name' => 'Food Service',
                        'code' => 'AGRI-SERV',
                        'level' => 2,
                        'children' => [
                            ['name' => 'Restaurants', 'code' => 'AGRI-SERV-RES', 'level' => 3],
                            ['name' => 'Food Delivery', 'code' => 'AGRI-SERV-DEL', 'level' => 3],
                            ['name' => 'Catering', 'code' => 'AGRI-SERV-CAT', 'level' => 3],
                        ]
                    ],
                ]
            ],
        ];

        $this->createSectors($sectors);
    }

    private function createSectors($sectors, $parentId = null)
    {
        foreach ($sectors as $sectorData) {
            $children = $sectorData['children'] ?? [];
            unset($sectorData['children']);
            
            $sectorData['parent_id'] = $parentId;
            // Ensure slug is present to satisfy schema requirements
            if (!isset($sectorData['slug'])) {
                $base = $sectorData['code'] ?? $sectorData['name'] ?? Str::random(8);
                $sectorData['slug'] = Str::slug($base);
            }
            $sector = BusinessSector::create($sectorData);
            
            if (!empty($children)) {
                $this->createSectors($children, $sector->id);
            }
        }
    }
}