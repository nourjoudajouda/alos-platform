<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\TenantSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-21 — Branding Settings للموقع الخارجي للتيننت
 */
class BrandingSettingsController extends Controller
{
    public function edit(): View
    {
        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 403);

        $settings = $tenant->getSettingsOrCreate();
        $pageConfigs = ['myLayout' => 'office', 'customizerHide' => true];

        return view('office.settings.branding', [
            'pageConfigs' => $pageConfigs,
            'tenant' => $tenant,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = auth()->user()->tenant;
        abort_unless($tenant, 403);

        $settings = $tenant->getSettingsOrCreate();

        $validated = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:20', 'regex:/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'secondary_color' => ['nullable', 'string', 'max:20', 'regex:/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'whatsapp' => ['nullable', 'string', 'max:64'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:128'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'public_site_enabled' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'favicon' => ['nullable', 'image', 'mimes:png', 'max:512'],
            'remove_favicon' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['remove_logo'])) {
            $settings->removeLogo();
        } elseif ($request->hasFile('logo')) {
            $settings->uploadLogo($request->file('logo'));
        }

        if (!empty($validated['remove_favicon'])) {
            $settings->removeFavicon();
        } elseif ($request->hasFile('favicon')) {
            $settings->uploadFavicon($request->file('favicon'));
        }

        $settings->update([
            'display_name' => $validated['display_name'] ?? null,
            'primary_color' => isset($validated['primary_color']) && $validated['primary_color'] ? ltrim($validated['primary_color'], '#') : null,
            'secondary_color' => isset($validated['secondary_color']) && $validated['secondary_color'] ? ltrim($validated['secondary_color'], '#') : null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'short_description' => $validated['short_description'] ?? null,
            'public_site_enabled' => $request->boolean('public_site_enabled', true),
        ]);

        return redirect()
            ->route('company.settings.branding.edit')
            ->with('success', __('Settings saved successfully.'));
    }
}
