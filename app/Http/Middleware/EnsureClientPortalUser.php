<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ALOS-S1-08 — Client Portal access control.
 * Ensures ONLY valid client portal users can access /portal/* routes.
 * Must run after auth middleware.
 */
class EnsureClientPortalUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, __('Authentication required.'));
        }

        if ($user->user_type !== User::USER_TYPE_CLIENT) {
            abort(403, __('This area is for client portal only.'));
        }

        if (! $user->client_id) {
            abort(403, __('Invalid portal account: client link missing.'));
        }

        if (! $user->tenant_id) {
            abort(403, __('Invalid portal account: tenant link missing.'));
        }

        if (! $user->isPortalActive()) {
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
