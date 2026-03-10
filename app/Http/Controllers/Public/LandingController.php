<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

/**
 * ALOS-S1-16 — Public Landing Page (الصفحة الرئيسية العامة)
 * الموقع الخارجي لكل Tenant في: /f/{slug} → TenantPublicSiteController
 */
class LandingController extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'front', 'customizerHide' => true];

        return view('core::content.public.landing', [
            'pageConfigs' => $pageConfigs,
        ]);
    }
}
