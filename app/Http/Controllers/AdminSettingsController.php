<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $keys = [
            'platform_fee_percentage',
            'seo_meta_title', 'seo_meta_description',
            'smtp_host','smtp_port','smtp_username','smtp_encryption',
            'mail_from_address','mail_from_name',
            'recaptcha_site_key','recaptcha_secret_key',
            'analytics_id','tawk_site_id',
            'brand_logo_url','brand_favicon_url','brand_primary_color',
            'platform_wallet_user_id',
        ];
        $values = collect($keys)->mapWithKeys(function ($k) {
            return [$k => Setting::get($k)];
        });
        return Inertia::render('Admin/Settings/Index', [
            'settings' => $values,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'platform_fee_percentage' => ['nullable','numeric','min:0','max:100'],
            'seo_meta_title' => ['nullable','string','max:255'],
            'seo_meta_description' => ['nullable','string','max:1000'],
            'smtp_host' => ['nullable','string','max:255'],
            'smtp_port' => ['nullable','integer'],
            'smtp_username' => ['nullable','string','max:255'],
            'smtp_encryption' => ['nullable','string','max:50'],
            'mail_from_address' => ['nullable','email','max:255'],
            'mail_from_name' => ['nullable','string','max:255'],
            'recaptcha_site_key' => ['nullable','string','max:255'],
            'recaptcha_secret_key' => ['nullable','string','max:255'],
            'analytics_id' => ['nullable','string','max:255'],
            'tawk_site_id' => ['nullable','string','max:255'],
            'brand_logo_url' => ['nullable','string','max:500'],
            'brand_favicon_url' => ['nullable','string','max:500'],
            'brand_primary_color' => ['nullable','string','max:50'],
            'platform_wallet_user_id' => ['nullable','integer','exists:users,id'],
        ]);

        foreach ($data as $k => $v) {
            Setting::set($k, $v);
        }

        return redirect()->back()->with('success', 'Settings saved');
    }
}
