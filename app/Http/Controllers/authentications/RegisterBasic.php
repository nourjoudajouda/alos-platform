<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
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
    public function index(): View
    {
        $pageConfigs = ['myLayout' => 'front', 'customizerHide' => true];
        return view('core::content.authentications.auth-register-basic', ['pageConfigs' => $pageConfigs]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:64', 'regex:/^[a-zA-Z0-9_-]+$/', 'unique:tenants,username'],
            'name' => ['required', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'username' => __('Username / Subdomain'),
            'name' => __('Office/Company name'),
            'admin_name' => __('Admin name'),
            'email' => __('Email'),
            'password' => __('Password'),
        ]);

        $username = trim($validated['username']);
        $domain = Str::lower(Str::slug($username, '-'));
        $baseDomain = $domain;
        $d = 0;
        while (Tenant::where('domain', $domain)->exists()) {
            $domain = $baseDomain . '-' . (++$d);
        }

        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $i = 0;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . (++$i);
        }

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'username' => $username,
            'domain' => $domain,
            'plan' => 'free',
        ]);

        $user = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'tenant_id' => $tenant->id,
            'user_type' => User::USER_TYPE_TENANT_STAFF,
        ]);

        $user->assignRole('admin');

        Auth::login($user, (bool) $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()
            ->intended(route('company.dashboard'))
            ->with('status', __('Registration complete. Welcome to your office dashboard.'));
    }
}
