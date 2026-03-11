<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMajorUpdateReportJob;
use App\Models\Admin;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Case management. User sees cases only for clients they have team access to.
 * Permissions: cases.view (view/list), cases.manage (create/edit/delete).
 */
class CaseController extends Controller
{
    private function authorizeClient(Client $client): void
    {
        $user = auth()->user();
        if ($user instanceof Admin) {
            return;
        }
        if ($user->isClientPortalUser()) {
            App::make(AuditLogService::class)->recordCompliance('access_client', __('Client portal user attempted office client access.'), 'client', $client->id, $client->tenant_id);
            abort(403, __('Access denied.'));
        }
        if (! $client->teamAccess()->where('user_id', $user->id)->exists()) {
            App::make(AuditLogService::class)->recordCompliance('access_client', __('Attempted access to client outside team.'), 'client', $client->id, $client->tenant_id);
            abort(404, __('Not found.'));
        }
    }

    private function authorizeCase(CaseModel $case): void
    {
        $this->authorizeClient($case->client);
    }

    /** Clients the current user can access (team access; Admin sees all). */
    private function accessibleClientIds(): array
    {
        $user = auth()->user();
        if ($user instanceof Admin) {
            return Client::pluck('id')->all();
        }
        return $user->clientAccess()->pluck('clients.id')->all();
    }

    public function index(Request $request): View
    {
        $clientIds = $this->accessibleClientIds();
        $query = CaseModel::query()
            ->with(['client', 'responsibleLawyer'])
            ->whereIn('client_id', $clientIds)
            ->orderByDesc('updated_at');

        if ($request->filled('client_id') && in_array((int) $request->get('client_id'), $clientIds, true)) {
            $query->where('client_id', $request->get('client_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('search')) {
            $term = $request->get('search');
            $query->where(function ($q) use ($term) {
                $q->where('case_number', 'like', "%{$term}%")
                    ->orWhere('case_type', 'like', "%{$term}%")
                    ->orWhere('court_name', 'like', "%{$term}%");
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $cases = $query->paginate($perPage)->withQueryString();

        $clients = Client::whereIn('id', $clientIds)->orderBy('name')->get();

        return view('core::content.cases.index', [
            'cases' => $cases,
            'perPage' => $perPage,
            'clients' => $clients,
            'filterClientId' => $request->get('client_id', ''),
            'filterStatus' => $request->get('status', ''),
            'search' => $request->get('search', ''),
        ]);
    }

    public function create(Request $request): View
    {
        $clientIds = $this->accessibleClientIds();
        $clients = Client::whereIn('id', $clientIds)->orderBy('name')->get();

        $preselectedClientId = null;
        $defaultCaseNumber = null;
        if ($request->filled('client_id') && in_array((int) $request->get('client_id'), $clientIds, true)) {
            $preselectedClientId = (int) $request->get('client_id');
            $preselectedClient = Client::with('tenant')->find($preselectedClientId);
            if ($preselectedClient) {
                $defaultCaseNumber = CaseModel::suggestCaseNumber(
                    $preselectedClient->tenant_id,
                    $preselectedClient->tenant?->name
                );
            }
        }

        $assignableUsers = User::whereNull('client_id')->orderBy('name')->get();
        $authUser = auth()->user();
        if (! $authUser instanceof Admin && $authUser->tenant_id) {
            $assignableUsers = User::where('tenant_id', $authUser->tenant_id)->whereNull('client_id')->orderBy('name')->get();
        }

        return view('core::content.cases.create', [
            'clients' => $clients,
            'preselectedClientId' => $preselectedClientId,
            'assignableUsers' => $assignableUsers,
            'defaultCaseNumber' => $defaultCaseNumber,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $clientIds = $this->accessibleClientIds();
        $validated = $request->validate([
            'client_id' => ['required', 'integer', Rule::in($clientIds)],
            'case_number' => ['required', 'string', 'max:255'],
            'case_type' => ['nullable', 'string', 'max:255'],
            'court_name' => ['nullable', 'string', 'max:255'],
            'responsible_lawyer_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(array_keys(CaseModel::STATUSES))],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $this->authorizeClient($client);

        $caseNumber = trim((string) $validated['case_number']);
        $caseNumberSuffix = CaseModel::parseCaseNumberSuffix($caseNumber);

        $case = $client->cases()->create([
            'tenant_id' => $client->tenant_id,
            'client_id' => $client->id,
            'case_number' => $caseNumber,
            'case_number_suffix' => $caseNumberSuffix,
            'case_type' => $validated['case_type'] ?? null,
            'court_name' => $validated['court_name'] ?? null,
            'responsible_lawyer_id' => $validated['responsible_lawyer_id'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        App::make(AuditLogService::class)->recordAudit(\App\Models\AuditLog::ACTION_CREATE_CASE, \App\Models\AuditLog::ENTITY_CASE, $case->id, [], [], $client->tenant_id);

        return redirect()
            ->route('admin.core.cases.show', $case)
            ->with('success', __('Case created successfully.'));
    }

    public function show(CaseModel $case): View
    {
        $this->authorizeCase($case);
        $case->load(['client', 'responsibleLawyer', 'tenant']);

        return view('core::content.cases.show', ['case' => $case]);
    }

    public function edit(CaseModel $case): View
    {
        $this->authorizeCase($case);
        $clientIds = $this->accessibleClientIds();
        $clients = Client::whereIn('id', $clientIds)->orderBy('name')->get();
        $assignableUsers = User::whereNull('client_id')->orderBy('name')->get();
        $authUser = auth()->user();
        if (! $authUser instanceof Admin && $authUser->tenant_id) {
            $assignableUsers = User::where('tenant_id', $authUser->tenant_id)->whereNull('client_id')->orderBy('name')->get();
        }

        return view('core::content.cases.edit', [
            'case' => $case,
            'clients' => $clients,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    public function update(Request $request, CaseModel $case): RedirectResponse
    {
        $this->authorizeCase($case);
        $clientIds = $this->accessibleClientIds();

        $validated = $request->validate([
            'client_id' => ['required', 'integer', Rule::in($clientIds)],
            'case_number' => ['required', 'string', 'max:255'],
            'case_type' => ['nullable', 'string', 'max:255'],
            'court_name' => ['nullable', 'string', 'max:255'],
            'responsible_lawyer_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(array_keys(CaseModel::STATUSES))],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $caseNumber = trim((string) $validated['case_number']);
        $caseNumberSuffix = CaseModel::parseCaseNumberSuffix($caseNumber);
        $oldStatus = $case->status;
        $oldValues = $case->only(['status', 'case_number', 'case_type', 'court_name', 'responsible_lawyer_id', 'description']);

        $case->update([
            'client_id' => $validated['client_id'],
            'tenant_id' => Client::find($validated['client_id'])->tenant_id,
            'case_number' => $caseNumber,
            'case_number_suffix' => $caseNumberSuffix,
            'case_type' => $validated['case_type'] ?? null,
            'court_name' => $validated['court_name'] ?? null,
            'responsible_lawyer_id' => $validated['responsible_lawyer_id'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        $newValues = $case->only(['status', 'case_number', 'case_type', 'court_name', 'responsible_lawyer_id', 'description']);
        App::make(AuditLogService::class)->recordAuditWithChanges(\App\Models\AuditLog::ACTION_UPDATE_CASE, \App\Models\AuditLog::ENTITY_CASE, $case->id, $oldValues, $newValues, $case->tenant_id);
        if ($oldStatus !== $validated['status']) {
            ProcessMajorUpdateReportJob::dispatch($case->client_id, 'case_status_change');
        }

        return redirect()
            ->route('admin.core.cases.show', $case)
            ->with('success', __('Case updated successfully.'));
    }

    public function destroy(CaseModel $case): RedirectResponse
    {
        $this->authorizeCase($case);
        $client = $case->client;
        $oldValues = $case->only(['case_number', 'case_type', 'court_name', 'status', 'client_id', 'tenant_id']);
        App::make(AuditLogService::class)->recordAudit(\App\Models\AuditLog::ACTION_DELETE, \App\Models\AuditLog::ENTITY_CASE, $case->id, $oldValues, [], $case->tenant_id);
        $case->delete();

        return redirect()
            ->route('admin.core.clients.show', [$client, 'tab' => 'cases'])
            ->with('success', __('Case deleted successfully.'));
    }
}
