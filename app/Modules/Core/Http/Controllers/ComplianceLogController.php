<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ComplianceLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-25 — Compliance Log: access violations, unauthorized attempts. Tenant isolation + compliance.view.
 */
class ComplianceLogController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = ComplianceLog::query()
            ->with(['tenant', 'user'])
            ->orderByDesc('created_at');

        if (! $user instanceof Admin) {
            if ($user instanceof User && $user->tenant_id) {
                $query->where('tenant_id', $user->tenant_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('tenant_id') && $user instanceof Admin) {
            $query->where('tenant_id', $request->get('tenant_id'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->filled('attempted_action')) {
            $query->where('attempted_action', 'like', '%' . $request->get('attempted_action') . '%');
        }
        if ($request->filled('target_entity')) {
            $query->where('target_entity', $request->get('target_entity'));
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
        $users = $user instanceof Admin ? User::whereNotNull('tenant_id')->orderBy('name')->get() : User::where('tenant_id', $user->tenant_id ?? 0)->orderBy('name')->get();

        return view('core::content.compliance-logs.index', [
            'logs' => $logs,
            'tenants' => $tenants,
            'users' => $users,
            'perPage' => $perPage,
        ]);
    }

    public function show(ComplianceLog $compliance_log): View
    {
        $user = auth()->user();
        if (! $user instanceof Admin && ($user->tenant_id !== $compliance_log->tenant_id)) {
            abort(404, __('Not found.'));
        }
        $compliance_log->load(['tenant', 'user']);

        return view('core::content.compliance-logs.show', [
            'log' => $compliance_log,
        ]);
    }
}
