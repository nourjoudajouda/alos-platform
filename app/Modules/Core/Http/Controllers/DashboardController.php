<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PlatformDashboardService;
use Illuminate\Contracts\View\View;

/**
 * ALOS-S1-31B — Platform (Super Admin) Dashboard.
 * Shows only platform-level metrics: law firms, subscriptions, contracts. No tenant operational data.
 */
class DashboardController extends Controller
{
    public function __invoke(PlatformDashboardService $platformDashboard): View
    {
        $metrics = $platformDashboard->getMetrics();
        return view('core::dashboard', compact('metrics'));
    }
}
