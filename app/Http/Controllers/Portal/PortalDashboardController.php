<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientDashboardService;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-08 / ALOS-S1-28 — Client Portal dashboard. User sees only their client's data.
 */
class PortalDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $client = $user->client;

        if (! $client instanceof Client) {
            abort(404, __('Client not found.'));
        }
        $tenant = $user->tenant;
        if ($tenant) {
            try {
                app(PlanLimitService::class)->ensureFeature($tenant, PlanLimitService::FEATURE_CLIENT_PORTAL);
            } catch (\RuntimeException $e) {
                abort(403, $e->getMessage());
            }
        }

        $dashboardService = new ClientDashboardService($user);
        $summary = $dashboardService->getSummary();

        return view('portal::dashboard.index', [
            'client' => $client,
            'user' => $user,
            'summary' => $summary,
        ]);
    }
}
