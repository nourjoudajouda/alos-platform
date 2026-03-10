<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * صفحات المكاتب (عملاء، قضايا، استشارات، إلخ) — فقط يوزر التيننت (جدول users مع tenant_id).
 * الأدمن (جدول admins) يُوجّه للداشبورد ويدير Tenants / Roles / Permissions فقط.
 */
class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.core.dashboard')
                ->with('info', __('This section is for office users. Use Tenants, Roles and Permissions to manage the platform.'));
        }

        return $next($request);
    }
}
