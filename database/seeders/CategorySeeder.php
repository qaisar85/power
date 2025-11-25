<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // GREEN PRIORITY 1 (1-9)
            ['name' => 'Home', 'slug' => 'home', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 1],
            ['name' => 'Sale', 'slug' => 'sale', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 2],
            ['name' => 'Rent', 'slug' => 'rent', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 3],
            ['name' => 'Inspection Company', 'slug' => 'inspection-company', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 4],
            ['name' => 'Oil Drilling Company', 'slug' => 'oil-drilling-company', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 5],
            ['name' => 'Logistics Company', 'slug' => 'logistics-company', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 6],
            ['name' => 'Tenders', 'slug' => 'tenders', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 7],
            ['name' => 'Branch (Regional Managers)', 'slug' => 'branch-regional-managers', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 8],
            ['name' => 'Contacts', 'slug' => 'contacts', 'priority' => 1, 'color' => '#28A745', 'sort_order' => 9],

            // YELLOW PRIORITY 2 (10-18)
            ['name' => 'Business for Sale', 'slug' => 'business-for-sale', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 10],
            ['name' => 'Auction', 'slug' => 'auction', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 11],
            ['name' => 'Stocks', 'slug' => 'stocks', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 12],
            ['name' => 'Investment', 'slug' => 'investment', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 13],
            ['name' => 'Job', 'slug' => 'job', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 14],
            ['name' => 'Freelancer', 'slug' => 'freelancer', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 15],
            ['name' => 'News', 'slug' => 'news', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 16],
            ['name' => 'Partnership', 'slug' => 'partnership', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 17],
            ['name' => 'Forum', 'slug' => 'forum', 'priority' => 2, 'color' => '#FFC107', 'sort_order' => 18],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}