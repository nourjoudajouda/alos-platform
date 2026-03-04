<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Set app locale from session (saved when user switches language).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));
        $supported = array_keys(config('localization.supported', ['en' => 'English']));

        if (in_array($locale, $supported, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
