<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * لوحة التيننت (/company) — فقط يوزرز المكتب (user_type = tenant_staff و tenant_id).
 * يضبط سياق Spatie teams (tenant_id) لفحص الصلاحيات.
 */
class EnsureTenantStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // ALOS-S1-08 — Block client portal users from office area
        if ($user->isClientPortalUser()) {
            return redirect()->route('portal.dashboard')
                ->with('message', __('Client portal users cannot access the office area.'));
        }

        if (! $user->isTenantStaff()) {
            return redirect()->route('login');
        }

        if ($user->tenant_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->tenant_id);
        }

        $tenant = $user->tenant;
        if (! $tenant || ! $tenant->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->withErrors(['email' => __('Your office account is currently disabled. Please contact support.')]);
        }

        return $next($request);
    }
}
