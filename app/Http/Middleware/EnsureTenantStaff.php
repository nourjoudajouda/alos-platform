<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * لوحة التيننت (/company) — فقط يوزرز المكتب (user_type = tenant_staff و tenant_id).
 */
class EnsureTenantStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isTenantStaff()) {
            return redirect()->route('login');
        }

        $tenant = Auth::user()->tenant;
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
