<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Share global locale and localized disclaimer with Inertia
        $locale = app()->getLocale();
        $disclaimers = config('legal.disclaimers', []);
        $globalDisclaimer = $disclaimers[$locale] ?? ($disclaimers['en'] ?? 'Investments involve risk. This is not a public offer.');

        Inertia::share([
            'locale' => $locale,
            'disclaimer' => $globalDisclaimer,
        ]);

        // Runtime settings loader: map admin settings to config
        try {
            app('router')->aliasMiddleware('recaptcha', \App\Http\Middleware\VerifyRecaptcha::class);
            $settings = [
                'smtp_host' => ['mail.mailers.smtp.host'],
                'smtp_port' => ['mail.mailers.smtp.port'],
                'smtp_username' => ['mail.mailers.smtp.username'],
                'smtp_encryption' => ['mail.mailers.smtp.encryption'],
                'mail_from_address' => ['mail.from.address'],
                'mail_from_name' => ['mail.from.name'],
                'recaptcha_site_key' => ['services.recaptcha.site_key'],
                'recaptcha_secret_key' => ['services.recaptcha.secret'],
                'analytics_id' => ['services.analytics.id'],
                'tawk_site_id' => ['services.tawk.site_id'],
                'brand_logo_url' => ['app.brand.logo_url'],
                'brand_favicon_url' => ['app.brand.favicon_url'],
                'brand_primary_color' => ['app.brand.primary_color'],
                'seo_meta_title' => ['app.seo.meta_title'],
                'seo_meta_description' => ['app.seo.meta_description'],
                'platform_fee_percentage' => ['commerce.platform_fee_percentage'],
                'platform_wallet_user_id' => ['commerce.platform_wallet_user_id'],
            ];

            foreach ($settings as $key => $paths) {
                $value = \App\Models\Setting::get($key);
                if ($value === null || $value === '') {
                    continue;
                }
                foreach ($paths as $path) {
                    config([$path => $value]);
                }
            }
        } catch (\Throwable $e) {
            // Avoid boot failures if settings table not yet migrated
        }
    }
}
