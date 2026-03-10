<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\View\View;

/**
 * ALOS-S1-20 / ALOS-S1-21 — Tenant Public Website
 * الموقع الخارجي الخاص بكل Tenant — يستخدم tenant_settings للـ Branding
 */
class TenantPublicSiteController extends Controller
{
    /**
     * Show the tenant public site — /{tenant_slug}
     * الشروط: tenant موجود، is_active، public_site_enabled (من settings)
     */
    public function show(string $slug): View
    {
        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant || !$tenant->is_active) {
            abort(404);
        }

        $settings = $tenant->getSettingsOrCreate();
        if (!$settings->hasPublicSiteEnabled()) {
            abort(404);
        }

        $pageConfigs = [
            'myLayout' => 'tenant-public',
            'customizerHide' => true,
        ];

        return view('core::content.public.tenant-site', [
            'pageConfigs' => $pageConfigs,
            'tenant' => $tenant,
            'settings' => $settings,
        ]);
    }
}
