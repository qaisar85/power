<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    protected array $supported = ['en', 'ru', 'ar', 'fr'];

    public function handle(Request $request, Closure $next)
    {
        $localeParam = $request->query('lang');

        if ($localeParam && in_array($localeParam, $this->supported, true)) {
            Session::put('locale', $localeParam);
        }

        $locale = Session::get('locale', config('app.locale'));

        if (!in_array($locale, $this->supported, true)) {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}