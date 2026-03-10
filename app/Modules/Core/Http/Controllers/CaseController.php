<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\Client;
use App\Models\User;
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
        if ($user->isClientPortalUser()) {
            abort(403, __('Access denied.'));
        }
        if (! $client->teamAccess()->where('user_id', $user->id)->exists()) {
            abort(404, __('Not found.'));
        }
    }

    private function authorizeCase(CaseModel $case): void
    {
        $this->authorizeClient($case->client);
    }

    /** Clients the current user can access (team access). */
    private function accessibleClientIds(): array
    {
        return auth()->user()->clientAccess()->pluck('clients.id')->all();
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
        if (auth()->user()->tenant_id) {
            $assignableUsers = User::where('tenant_id', auth()->user()->tenant_id)->whereNull('client_id')->orderBy('name')->get();
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
        if (auth()->user()->tenant_id) {
            $assignableUsers = User::where('tenant_id', auth()->user()->tenant_id)->whereNull('client_id')->orderBy('name')->get();
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

        return redirect()
            ->route('admin.core.cases.show', $case)
            ->with('success', __('Case updated successfully.'));
    }

    public function destroy(CaseModel $case): RedirectResponse
    {
        $this->authorizeCase($case);
        $client = $case->client;
        $case->delete();

        return redirect()
            ->route('admin.core.clients.show', [$client, 'tab' => 'cases'])
            ->with('success', __('Case deleted successfully.'));
    }
}
