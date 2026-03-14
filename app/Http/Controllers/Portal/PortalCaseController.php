<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CaseModel;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-28 — Client Portal: My Cases (read-only list).
 * Client sees only their own cases. No internal notes or other clients' data.
 */
class PortalCaseController extends Controller
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

        $cases = CaseModel::query()
            ->where('client_id', (int) $client->id)
            ->when($user->tenant_id, fn ($q) => $q->where('tenant_id', $user->tenant_id))
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('portal::cases.index', [
            'client' => $client,
            'user' => $user,
            'cases' => $cases,
        ]);
    }
}
