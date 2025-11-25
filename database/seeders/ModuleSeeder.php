<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Marketplace',
                'slug' => 'marketplace',
                'path' => '/marketplace',
                'description' => 'Global business marketplace for B2B connections',
                'icon' => 'briefcase',
                'integration_type' => 'native',
                'config' => [
                    'api_endpoint' => '/api/marketplace',
                    'features' => ['search', 'categories', 'messaging', 'reviews']
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'Jobs',
                'slug' => 'jobs',
                'path' => '/jobs',
                'description' => 'Professional and freelance job opportunities',
                'icon' => 'users',
                'integration_type' => 'native',
                'config' => [
                    'api_endpoint' => '/api/jobs',
                    'features' => ['job_search', 'applications', 'resume_builder', 'freelance']
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'Tenders',
                'slug' => 'tenders',
                'path' => '/tenders',
                'description' => 'Government and private sector tender opportunities',
                'icon' => 'file-text',
                'integration_type' => 'native',
                'config' => [
                    'api_endpoint' => '/api/tenders',
                    'features' => ['tender_search', 'bid_management', 'notifications']
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'Auctions',
                'slug' => 'auctions',
                'path' => '/auctions',
                'description' => 'Business asset and equipment auctions',
                'icon' => 'calendar',
                'integration_type' => 'native',
                'config' => [
                    'api_endpoint' => '/api/auctions',
                    'features' => ['live_bidding', 'asset_catalog', 'payment_processing']
                ],
                'sort_order' => 4,
            ],
            [
                'name' => 'Training',
                'slug' => 'training',
                'path' => '/training',
                'description' => 'Professional development and business training',
                'icon' => 'book-open',
                'integration_type' => 'native',
                'config' => [
                    'api_endpoint' => '/api/training',
                    'features' => ['courses', 'certifications', 'webinars', 'progress_tracking']
                ],
                'sort_order' => 5,
            ],
            [
                'name' => 'News',
                'slug' => 'news',
                'path' => '/news',
                'description' => 'Global business news and market insights',
                'icon' => 'newspaper',
                'integration_type' => 'native',
                'config' => [
                    'api_endpoint' => '/api/news',
                    'features' => ['news_feed', 'market_data', 'analysis', 'alerts']
                ],
                'sort_order' => 6,
            ],
            [
                'name' => 'Freelance',
                'slug' => 'freelance',
                'path' => '/freelance',
                'description' => 'Services and project marketplace for oil & gas freelancers',
                'icon' => 'briefcase',
                'integration_type' => 'native',
                'config' => [
                    'api_endpoint' => '/api/freelance',
                    'features' => ['services', 'projects', 'proposals', 'orders', 'messaging', 'reviews', 'moderation']
                ],
                'sort_order' => 7,
            ],
        ];

        foreach ($modules as $moduleData) {
            Module::create($moduleData);
        }
    }
}