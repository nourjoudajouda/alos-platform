<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ALOS-S1-08 — Prevent client portal users from using the main app login.
 * They must use /portal/login instead.
 */
class EnsureNotClientPortalUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        if ($request->user()->isClientPortalUser()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('portal.login')
                ->with('message', __('Please use the client portal to sign in.'));
        }

        return $next($request);
    }
}
