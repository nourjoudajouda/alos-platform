<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginBasic extends Controller
{
    public function index()
    {
        return view('core::content.authentications.auth-login-landing');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($validated, (bool) $request->boolean('remember'))) {
            app(AuditLogService::class)->recordCompliance('login_failed', __('Tenant login failed.'), null, null, null, null);
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // ALOS-S1-08 — Client portal users must use /portal/login
        if (Auth::user()->isClientPortalUser()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            app(AuditLogService::class)->recordCompliance('portal_login_wrong_url', __('Client portal user tried tenant login.'), null, null, null, null);
            throw ValidationException::withMessages([
                'email' => [__('Please sign in via the client portal.')],
            ]);
        }

        // ALOS-S1-18 — هذه الصفحة للمستخدمين الداخليين فقط (tenant_staff)
        if (! Auth::user()->isTenantStaff()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            app(AuditLogService::class)->recordCompliance('tenant_login_wrong_account', __('Non-tenant user tried tenant login.'), null, null, null, null);
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
            app(AuditLogService::class)->recordCompliance('tenant_disabled', __('Login attempt for disabled tenant.'), 'tenant', $tenant?->id, $tenant?->id, $user->id);
            throw ValidationException::withMessages([
                'email' => [__('Your office account is currently disabled. Please contact support.')],
            ]);
        }

        $request->session()->regenerate();
        app(AuditLogService::class)->recordAudit(AuditLog::ACTION_LOGIN, AuditLog::ENTITY_USER, $user->id, [], [], $tenant->id);
        return redirect()->intended(route('company.dashboard'));
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user?->tenant_id;
        app(AuditLogService::class)->recordAudit(AuditLog::ACTION_LOGOUT, AuditLog::ENTITY_USER, $user?->id ?? 0, [], [], $tenantId);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
