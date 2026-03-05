<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-08 — Client Portal dashboard. User sees only their client's data.
 */
class PortalDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $client = $user->client;

        if (! $client) {
            abort(404, __('Client not found.'));
        }

        return view('portal::dashboard.index', [
            'client' => $client,
            'user' => $user,
        ]);
    }
}
