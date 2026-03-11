<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-25 — Audit Log: who did what, on which entity, when. Tenant isolation + audit.view.
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = AuditLog::query()
            ->with(['tenant', 'user'])
            ->orderByDesc('created_at');

        if (! $user instanceof Admin) {
            if ($user instanceof User && $user->tenant_id) {
                $query->where('tenant_id', $user->tenant_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('tenant_id')) {
            if ($user instanceof Admin) {
                $query->where('tenant_id', $request->get('tenant_id'));
            }
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->get('entity_type'));
        }
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->get('action') . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        $perPage = (int) $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;
        $logs = $query->paginate($perPage)->withQueryString();

        $tenants = $user instanceof Admin ? Tenant::orderBy('name')->get() : collect();
        $entityTypes = AuditLog::query()->distinct()->pluck('entity_type')->filter()->sort()->values();
        $users = $user instanceof Admin ? User::whereNotNull('tenant_id')->orderBy('name')->get() : User::where('tenant_id', $user->tenant_id ?? 0)->orderBy('name')->get();

        return view('core::content.audit-logs.index', [
            'logs' => $logs,
            'tenants' => $tenants,
            'entityTypes' => $entityTypes,
            'users' => $users,
            'perPage' => $perPage,
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        $user = auth()->user();
        if (! $user instanceof Admin && ($user->tenant_id !== $auditLog->tenant_id)) {
            abort(404, __('Not found.'));
        }
        $auditLog->load(['tenant', 'user']);

        return view('core::content.audit-logs.show', [
            'log' => $auditLog,
        ]);
    }
}
