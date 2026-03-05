<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ALOS-S1-08 — Restrict portal routes to authenticated client portal users with active account.
 */
class EnsurePortalClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('portal.login');
        }

        if (! $request->user()->isClientPortalUser()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('message', __('This area is for client portal only.'));
        }

        if (! $request->user()->isPortalActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('portal.login')
                ->withErrors(['email' => __('Your portal account is disabled. Please contact the office.')]);
        }

        return $next($request);
    }
}
