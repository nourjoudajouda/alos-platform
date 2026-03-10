<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * لوحة الإدارة: مسموح لأدمن (جدول admins) أو يوزر تيننت (users مع tenant_id).
 */
class EnsureAdminOrTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        if (Auth::guard('web')->check() && Auth::user()->tenant_id) {
            return $next($request);
        }

        return redirect()->route('admin.login');
    }
}
