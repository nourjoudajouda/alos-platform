<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CaseModel;
use App\Models\Document;
use App\Models\MessageThread;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-28 — Client Portal: My Cases (read-only).
 * Client sees only their own cases. No internal notes, internal documents, or other clients' data.
 */
class PortalCaseController extends Controller
{
    /** Safe case columns for portal (no description/internal metadata). */
    private const PORTAL_CASE_SELECT = [
        'id', 'tenant_id', 'client_id', 'case_number', 'case_type', 'status',
        'responsible_lawyer_id', 'updated_at',
    ];

    private function getClient(\Illuminate\Contracts\Auth\Authenticatable $user): Client
    {
        $client = $user->client;
        if (! $client instanceof Client) {
            abort(404, __('Client not found.'));
        }
        return $client;
    }

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

        $cases = CaseModel::query()
            ->select(self::PORTAL_CASE_SELECT)
            ->where('client_id', (int) $client->id)
            ->where('tenant_id', (int) $user->tenant_id)
            ->with('responsibleLawyer:id,name')
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('portal::cases.index', [
            'client' => $client,
            'user' => $user,
            'cases' => $cases,
        ]);
    }

    public function show(Request $request, CaseModel $case): View
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

        // Enforce client and tenant isolation: only allow viewing own case
        if ((int) $case->client_id !== (int) $client->id || (int) $case->tenant_id !== (int) $user->tenant_id) {
            abort(404);
        }

        // Reload with only safe columns (in case route binding loaded full model)
        $case->setVisible(self::PORTAL_CASE_SELECT);

        // Shared documents only (visibility = shared)
        $sharedDocuments = $case->documents()
            ->where('visibility', Document::VISIBILITY_SHARED)
            ->select(['id', 'name', 'file_name', 'file_size', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        // Sessions: only safe fields (no notes, no internal preparation)
        $sessions = $case->sessions()
            ->select(['id', 'case_id', 'session_date', 'session_time', 'court_name', 'status'])
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get();

        // Related conversations (threads for this case and this client)
        $threads = MessageThread::query()
            ->where('client_id', (int) $client->id)
            ->where('case_id', (int) $case->id)
            ->select(['id', 'subject', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        $case->load('responsibleLawyer:id,name');

        return view('portal::cases.show', [
            'case' => $case,
            'client' => $client,
            'user' => $user,
            'sharedDocuments' => $sharedDocuments,
            'sessions' => $sessions,
            'threads' => $threads,
        ]);
    }
}
