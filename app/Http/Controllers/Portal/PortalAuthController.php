<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * ALOS-S1-08 — Client Portal login at /portal/login.
 * Only client users (client_id set) with portal_active can sign in here.
 */
class PortalAuthController extends Controller
{
    public function index(): View
    {
        $pageConfigs = ['myLayout' => 'blank'];
        return view('portal::auth.login', ['pageConfigs' => $pageConfigs]);
    }

    public function store(Request $request): RedirectResponse
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

        $user = Auth::user();
        if (! $user->isClientPortalUser()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => [__('This login is for client portal only. Use the main login for staff.')],
            ]);
        }

        if (! $user->isPortalActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => [__('Your portal account is disabled. Please contact the office.')],
            ]);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('portal.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }
}
