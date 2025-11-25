<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\PackageFeature;

class PackagesSeeder extends Seeder
{
    public function run(): void
    {
        // Free Demo Package
        $demo = Package::create([
            'name' => 'Free Demo Package',
            'slug' => 'demo-free',
            'description' => 'Test the platform with virtual balance. Contacts hidden, admin facilitates communication.',
            'price' => 0.00,
            'currency' => 'USD',
            'duration_days' => 30,
            'listing_limit' => 1,
            'cards_limit' => 1,
            'tenders_limit' => 0,
            'auctions_limit' => 0,
            'photos_limit' => 5,
            'description_chars_limit' => 200,
            'contacts_visible' => false,
            'is_vip' => false,
            'is_active' => true,
            'features' => [
                '1 product listing',
                'Valid for 30 days',
                'Up to 5 photos',
                '200 character description',
                'Contacts hidden (admin mediated)',
                'Basic support'
            ]
        ]);

        // Standard Package
        $standard = Package::create([
            'name' => 'Standard Package',
            'slug' => 'standard',
            'description' => 'Perfect for small businesses starting out',
            'price' => 99.00,
            'currency' => 'USD',
            'duration_days' => 30,
            'listing_limit' => 10,
            'cards_limit' => 10,
            'tenders_limit' => 2,
            'auctions_limit' => 2,
            'photos_limit' => 15,
            'description_chars_limit' => 350,
            'contacts_visible' => true,
            'is_vip' => false,
            'is_active' => true,
            'features' => [
                '10 product listings',
                'Valid for 30 days',
                'Up to 15 photos per listing',
                '350 character description',
                'PDF and video uploads',
                'Contacts visible',
                'Email support',
                '2 tender submissions',
                '2 auction participations'
            ]
        ]);

        // Expanded Package
        $expanded = Package::create([
            'name' => 'Expanded Package',
            'slug' => 'expanded',
            'description' => 'More listings and features for growing businesses',
            'price' => 299.00,
            'currency' => 'USD',
            'duration_days' => 30,
            'listing_limit' => 50,
            'cards_limit' => 50,
            'tenders_limit' => 10,
            'auctions_limit' => 10,
            'photos_limit' => 30,
            'description_chars_limit' => 700,
            'contacts_visible' => true,
            'is_vip' => false,
            'is_active' => true,
            'features' => [
                '50 product listings',
                'Valid for 30 days',
                'Up to 30 photos per listing',
                '700 character description',
                'PDF and video uploads',
                'Priority support',
                '10 tender submissions',
                '10 auction participations',
                'Basic analytics'
            ]
        ]);

        // Premium Package
        $premium = Package::create([
            'name' => 'Premium Package',
            'slug' => 'premium',
            'description' => 'Professional package with enhanced features',
            'price' => 599.00,
            'currency' => 'USD',
            'duration_days' => 60,
            'listing_limit' => 200,
            'cards_limit' => 200,
            'tenders_limit' => 50,
            'auctions_limit' => 50,
            'photos_limit' => 80,
            'description_chars_limit' => 3000,
            'contacts_visible' => true,
            'is_vip' => false,
            'is_active' => true,
            'features' => [
                '200 product listings',
                'Valid for 60 days',
                'Up to 80 photos per listing',
                '3000 character description',
                'Unlimited PDF and video uploads',
                'Featured listings (20)',
                'Priority support',
                '50 tender submissions',
                '50 auction participations',
                'Advanced analytics',
                'API access'
            ]
        ]);

        // VIP Package Level 1
        $vip1 = Package::create([
            'name' => 'VIP Package Level 1',
            'slug' => 'vip-1',
            'description' => 'Maximum visibility with premium features',
            'price' => 1499.00,
            'currency' => 'USD',
            'duration_days' => 90,
            'listing_limit' => -1, // Unlimited
            'cards_limit' => -1,
            'tenders_limit' => -1,
            'auctions_limit' => -1,
            'photos_limit' => 200,
            'description_chars_limit' => 7000,
            'contacts_visible' => true,
            'is_vip' => true,
            'vip_level' => 1,
            'is_active' => true,
            'features' => [
                'Unlimited product listings',
                'Valid for 90 days',
                'Up to 200 photos per listing',
                '7000 character description',
                'Unlimited media uploads',
                'All listings featured',
                'Top search placement',
                'Dedicated account manager',
                'Unlimited tender submissions',
                'Unlimited auction participations',
                'Premium analytics',
                'API access',
                'Priority moderation'
            ]
        ]);

        PackageFeature::create([
            'package_id' => $vip1->id,
            'feature_name' => 'Homepage Banner',
            'feature_value' => 'Yes',
            'description' => 'Your products featured on homepage banner'
        ]);

        // VIP Package Level 2
        $vip2 = Package::create([
            'name' => 'VIP Package Level 2',
            'slug' => 'vip-2',
            'description' => 'VIP 1 + Regional promotion services',
            'price' => 2999.00,
            'currency' => 'USD',
            'duration_days' => 90,
            'listing_limit' => -1,
            'cards_limit' => -1,
            'tenders_limit' => -1,
            'auctions_limit' => -1,
            'photos_limit' => 200,
            'description_chars_limit' => 7000,
            'contacts_visible' => true,
            'is_vip' => true,
            'vip_level' => 2,
            'is_active' => true,
            'features' => [
                'All VIP 1 features',
                'Regional promotion (up to 3 countries)',
                '100 printed catalogs',
                'Database collection (500 companies)',
                '100 commercial offers sent',
                'Email marketing campaign'
            ]
        ]);

        // VIP Package Level 3
        $vip3 = Package::create([
            'name' => 'VIP Package Level 3',
            'slug' => 'vip-3',
            'description' => 'VIP 2 + Agent meetings and presentations',
            'price' => 4999.00,
            'currency' => 'USD',
            'duration_days' => 90,
            'listing_limit' => -1,
            'cards_limit' => -1,
            'tenders_limit' => -1,
            'auctions_limit' => -1,
            'photos_limit' => 200,
            'description_chars_limit' => 7000,
            'contacts_visible' => true,
            'is_vip' => true,
            'vip_level' => 3,
            'is_active' => true,
            'features' => [
                'All VIP 2 features',
                'Regional promotion (up to 5 countries)',
                '300 printed catalogs',
                'Database collection (1000 companies)',
                '300 commercial offers sent',
                '50 cold calls',
                '10 meetings with decision makers',
                'Agent presentation services'
            ]
        ]);

        // VIP Package Level 4
        $vip4 = Package::create([
            'name' => 'VIP Package Level 4',
            'slug' => 'vip-4',
            'description' => 'VIP 3 + Dedicated agent support',
            'price' => 7999.00,
            'currency' => 'USD',
            'duration_days' => 180,
            'listing_limit' => -1,
            'cards_limit' => -1,
            'tenders_limit' => -1,
            'auctions_limit' => -1,
            'photos_limit' => 200,
            'description_chars_limit' => 7000,
            'contacts_visible' => true,
            'is_vip' => true,
            'vip_level' => 4,
            'is_active' => true,
            'features' => [
                'All VIP 3 features',
                'Valid for 180 days',
                'Regional promotion (up to 10 countries)',
                '500 printed catalogs',
                'Database collection (2000 companies)',
                '500 commercial offers sent',
                '100 cold calls',
                '25 meetings with decision makers',
                'Dedicated agent (40 hours/month)',
                'Trade show representation'
            ]
        ]);

        // VIP Package Level 5 (Ultimate)
        $vip5 = Package::create([
            'name' => 'VIP Package Level 5 - Ultimate',
            'slug' => 'vip-5',
            'description' => 'Complete global market penetration package',
            'price' => 14999.00,
            'currency' => 'USD',
            'duration_days' => 360,
            'listing_limit' => -1,
            'cards_limit' => -1,
            'tenders_limit' => -1,
            'auctions_limit' => -1,
            'photos_limit' => 200,
            'description_chars_limit' => 7000,
            'contacts_visible' => true,
            'is_vip' => true,
            'vip_level' => 5,
            'is_active' => true,
            'features' => [
                'All VIP 4 features',
                'Valid for 360 days (1 year)',
                'Global promotion (20+ countries)',
                '1000 printed catalogs',
                'Database collection (5000 companies)',
                '1000 commercial offers sent',
                '300 cold calls',
                '50 meetings with decision makers',
                'Dedicated agent (80 hours/month)',
                'Multiple trade show representations',
                'Custom marketing campaigns',
                'Monthly performance reports',
                'White-label solutions available'
            ]
        ]);

        // VIP Agent Services (Custom)
        $vipAgent = Package::create([
            'name' => 'VIP Agent Services (Custom)',
            'slug' => 'vip-agent-custom',
            'description' => 'Build your own package of agent services',
            'price' => 0.00, // Price calculated based on selections
            'currency' => 'USD',
            'duration_days' => 30,
            'listing_limit' => 0,
            'cards_limit' => 0,
            'tenders_limit' => 0,
            'auctions_limit' => 0,
            'photos_limit' => 0,
            'description_chars_limit' => 0,
            'contacts_visible' => true,
            'is_vip' => true,
            'vip_level' => 0,
            'is_active' => true,
            'features' => [
                'Customizable services',
                'Select target countries and cities',
                'Choose number of meetings',
                'Select catalog print quantity',
                'Database collection',
                'Commercial offer distribution',
                'Cold calling campaigns',
                'Agent rental by hours/days/weeks',
                'Flexible pricing based on selections'
            ]
        ]);

        $this->command->info('Packages seeded successfully!');
    }
}
