<?php

namespace App\Http\Controllers\language;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * Switch application locale and set RTL cookie when needed.
     */
    public function swap(Request $request, string $locale): RedirectResponse
    {
        $supported = array_keys(config('localization.supported', ['en' => 'English']));

        if (! in_array($locale, $supported, true)) {
            abort(400, 'Unsupported locale');
        }

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        $rtlLocales = config('localization.rtl_locales', ['ar']);
        $cookieName = config('localization.direction_cookie', 'direction');
        $direction = in_array($locale, $rtlLocales, true) ? 'true' : 'false';

        return redirect()->back()->cookie($cookieName, $direction, 60 * 24 * 365, '/');
    }
}
