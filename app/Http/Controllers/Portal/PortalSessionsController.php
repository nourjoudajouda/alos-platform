<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\CaseSession;
use App\Models\Client;
use App\Services\PlanLimitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS — Client Portal: Sessions (Court Hearings).
 *
 * Exposes safe session data for the authenticated client's cases only.
 * Tenant and client isolation enforced. No internal notes or staff fields exposed.
 */
class PortalSessionsController extends Controller
{
    /**
     * Safe session attributes for portal (no notes, assigned_to, or internal data).
     */
    private const PORTAL_SESSION_SELECT = ['id', 'case_id', 'session_date', 'session_time', 'court_name', 'status'];

    private function getClient(\Illuminate\Contracts\Auth\Authenticatable $user): Client
    {
        $client = $user->client;
        if (! $client instanceof Client) {
            abort(404, __('Client not found.'));
        }
        return $client;
    }

    /**
     * List upcoming sessions for the authenticated client.
     * Only sessions where case.client_id == authenticated client and case.tenant_id == current tenant.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $client = $this->getClient($user);

        if (! $user->tenant_id) {
            abort(403, __('Access denied.'));
        }

        $tenant = $user->tenant;
        if ($tenant) {
            try {
                app(PlanLimitService::class)->ensureFeature($tenant, PlanLimitService::FEATURE_CLIENT_PORTAL);
            } catch (\RuntimeException $e) {
                abort(403, $e->getMessage());
            }
        }

        $clientId = (int) $client->id;
        $tenantId = (int) $user->tenant_id;

        $sessions = CaseSession::query()
            ->select(self::PORTAL_SESSION_SELECT)
            ->with(['case:id,case_number,case_type'])
            ->whereHas('case', fn ($q) => $q
                ->where('client_id', $clientId)
                ->where('tenant_id', $tenantId))
            ->where('status', CaseSession::STATUS_SCHEDULED)
            ->where('session_date', '>=', now()->startOfDay())
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->paginate(15)
            ->withQueryString();

        $sessions->getCollection()->transform(function (CaseSession $s) {
            $date = $s->session_date ? Carbon::parse($s->session_date) : null;
            $badge = $this->sessionDateBadge($date);
            return [
                'id' => $s->id,
                'session_date' => $s->session_date?->format('Y-m-d'),
                'session_date_formatted' => $s->session_date?->translatedFormat('d M Y'),
                'session_time' => $s->session_time ? \Carbon\Carbon::parse($s->session_time)->format('H:i') : null,
                'court_name' => $s->court_name,
                'case_number' => $s->case?->case_number,
                'case_title' => $s->case?->case_type ?? $s->case?->case_number,
                'badge' => $badge,
            ];
        });

        return view('portal::sessions.index', [
            'client' => $client,
            'user' => $user,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Return badge key: 'today' | 'tomorrow' | 'upcoming'.
     */
    private function sessionDateBadge(?Carbon $date): string
    {
        if (! $date) {
            return 'upcoming';
        }
        $today = now()->startOfDay();
        if ($date->isSameDay($today)) {
            return 'today';
        }
        if ($date->isSameDay($today->copy()->addDay())) {
            return 'tomorrow';
        }
        return 'upcoming';
    }
}
