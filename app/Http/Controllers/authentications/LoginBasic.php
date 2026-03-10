<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginBasic extends Controller
{
    public function index()
    {
        $pageConfigs = ['myLayout' => 'front', 'customizerHide' => true];
        return view('core::content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($validated, (bool) $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // ALOS-S1-08 — Client portal users must use /portal/login
        if (Auth::user()->isClientPortalUser()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => [__('Please sign in via the client portal.')],
            ]);
        }

        // ALOS-S1-18 — هذه الصفحة للمستخدمين الداخليين فقط (tenant_staff)
        if (! Auth::user()->isTenantStaff()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => [__('Use the admin panel login for this account.')],
            ]);
        }

        $user = Auth::user();
        $tenant = $user->tenant;

        // التحقق من حالة Tenant: غير نشط => منع الدخول
        if (! $tenant || ! $tenant->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => [__('Your office account is currently disabled. Please contact support.')],
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('company.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
