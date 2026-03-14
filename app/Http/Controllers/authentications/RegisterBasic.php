<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;
use App\Services\SystemSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * ALOS-S1-17 — Tenant Self-Registration & Onboarding.
 * إنشاء Tenant جديد + أول مستخدم Admin (دور admin) + تسجيل دخول تلقائي → لوحة المكتب.
 */
class RegisterBasic extends Controller
{
    public function index(SystemSettingsService $settings): View|RedirectResponse
    {
        if (! $settings->get('allow_tenant_registration', true)) {
            return redirect()->route('home')->with('info', __('Tenant registration is currently disabled.'));
        }

        return view('core::content.authentications.auth-register-landing');
    }

    public function store(Request $request, SystemSettingsService $settings): RedirectResponse
    {
        if (! $settings->get('allow_tenant_registration', true)) {
            return redirect()->route('home')->with('info', __('Tenant registration is currently disabled.'));
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:tenants,username'],
            'name' => ['required', 'string', 'max:100'],
            'admin_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'username' => __('Subdomain'),
            'name' => __('Tenant name'),
            'admin_name' => __('Managing partner'),
            'email' => __('Email'),
            'password' => __('Password'),
        ]);

        $username = trim($validated['username']);
        $subdomain = Str::lower(Str::limit($username, 50, ''));
        $domain = Str::lower(Str::slug($username, '-'));
        $baseDomain = $domain;
        $d = 0;
        while (Tenant::where('domain', $domain)->exists()) {
            $domain = $baseDomain . '-' . (++$d);
        }

        $slug = Str::slug(Str::limit($validated['name'], 80, ''));
        $baseSlug = $slug;
        $i = 0;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . (++$i);
        }

        $startDate = now()->toDateString();
        $endDate = now()->addYear()->toDateString();

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'subdomain' => $subdomain,
            'username' => $username,
            'domain' => $domain,
            'email' => $validated['email'],
            'managing_partner' => $validated['admin_name'],
            'plan' => 'free',
            'status' => 'active',
            'user_limit' => (int) config('alos.tenant_defaults.user_limit', 10),
            'lawyer_limit' => (int) config('alos.tenant_defaults.lawyer_limit', 5),
            'storage_limit' => (int) config('alos.tenant_defaults.storage_limit', 1024),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'public_site_enabled' => true,
        ]);

        $user = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'tenant_id' => $tenant->id,
            'user_type' => User::USER_TYPE_TENANT_STAFF,
        ]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
        $user->assignRole('admin');

        Auth::login($user, (bool) $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()
            ->intended(route('company.dashboard'))
            ->with('status', __('Registration complete. Welcome to your dashboard.'));
    }
}
