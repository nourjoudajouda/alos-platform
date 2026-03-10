<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ALOS-S1-14 — Consultations Management.
 * User sees consultations only for clients they have team access to.
 * Permissions: consultations.view, consultations.manage.
 */
class ConsultationController extends Controller
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

    private function authorizeConsultation(Consultation $consultation): void
    {
        $this->authorizeClient($consultation->client);
    }

    /** Client IDs the current user can access (team access). */
    private function accessibleClientIds(): array
    {
        return auth()->user()->clientAccess()->pluck('clients.id')->all();
    }

    public function index(Request $request): View
    {
        $clientIds = $this->accessibleClientIds();
        $query = Consultation::query()
            ->with(['client', 'responsibleUser'])
            ->whereIn('client_id', $clientIds)
            ->orderByDesc('consultation_date')
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
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%");
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $consultations = $query->paginate($perPage)->withQueryString();

        $clients = Client::whereIn('id', $clientIds)->orderBy('name')->get();

        return view('core::content.consultations.index', [
            'consultations' => $consultations,
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

        $preselectedClientId = $request->filled('client_id') && in_array((int) $request->get('client_id'), $clientIds, true)
            ? (int) $request->get('client_id') : null;

        $assignableUsers = User::whereNull('client_id')->orderBy('name')->get();
        if (auth()->user()->tenant_id) {
            $assignableUsers = User::where('tenant_id', auth()->user()->tenant_id)->whereNull('client_id')->orderBy('name')->get();
        }

        return view('core::content.consultations.create', [
            'clients' => $clients,
            'preselectedClientId' => $preselectedClientId,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $clientIds = $this->accessibleClientIds();
        $validated = $request->validate([
            'client_id' => ['required', 'integer', Rule::in($clientIds)],
            'consultation_date' => ['required', 'date'],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(Consultation::STATUSES))],
            'is_shared_with_client' => ['nullable', 'boolean'],
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $this->authorizeClient($client);

        $consultation = $client->consultations()->create([
            'tenant_id' => $client->tenant_id,
            'client_id' => $client->id,
            'consultation_date' => $validated['consultation_date'],
            'responsible_user_id' => $validated['responsible_user_id'] ?? null,
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'status' => $validated['status'],
            'is_shared_with_client' => $request->boolean('is_shared_with_client', false),
        ]);

        return redirect()
            ->route('admin.core.consultations.show', $consultation)
            ->with('success', __('Consultation created successfully.'));
    }

    public function show(Consultation $consultation): View
    {
        $this->authorizeConsultation($consultation);
        $consultation->load(['client', 'responsibleUser', 'tenant', 'documents', 'messageThreads']);

        $availableThreads = $consultation->client->messageThreads()
            ->where(function ($q) use ($consultation) {
                $q->whereNull('consultation_id')->orWhere('consultation_id', $consultation->id);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('core::content.consultations.show', [
            'consultation' => $consultation,
            'availableThreads' => $availableThreads,
        ]);
    }

    public function edit(Consultation $consultation): View
    {
        $this->authorizeConsultation($consultation);
        $clientIds = $this->accessibleClientIds();
        $clients = Client::whereIn('id', $clientIds)->orderBy('name')->get();
        $assignableUsers = User::whereNull('client_id')->orderBy('name')->get();
        if (auth()->user()->tenant_id) {
            $assignableUsers = User::where('tenant_id', auth()->user()->tenant_id)->whereNull('client_id')->orderBy('name')->get();
        }

        return view('core::content.consultations.edit', [
            'consultation' => $consultation,
            'clients' => $clients,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    public function update(Request $request, Consultation $consultation): RedirectResponse
    {
        $this->authorizeConsultation($consultation);
        $clientIds = $this->accessibleClientIds();

        $validated = $request->validate([
            'client_id' => ['required', 'integer', Rule::in($clientIds)],
            'consultation_date' => ['required', 'date'],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(array_keys(Consultation::STATUSES))],
            'is_shared_with_client' => ['nullable', 'boolean'],
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $consultation->update([
            'client_id' => $validated['client_id'],
            'tenant_id' => $client->tenant_id,
            'consultation_date' => $validated['consultation_date'],
            'responsible_user_id' => $validated['responsible_user_id'] ?? null,
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'status' => $validated['status'],
            'is_shared_with_client' => $request->boolean('is_shared_with_client', false),
        ]);

        return redirect()
            ->route('admin.core.consultations.show', $consultation)
            ->with('success', __('Consultation updated successfully.'));
    }

    public function destroy(Consultation $consultation): RedirectResponse
    {
        $this->authorizeConsultation($consultation);
        $client = $consultation->client;
        $consultation->delete();

        return redirect()
            ->route('admin.core.clients.show', [$client, 'tab' => 'consultations'])
            ->with('success', __('Consultation deleted successfully.'));
    }

    /**
     * Link existing message thread to consultation.
     */
    public function linkThread(Request $request, Consultation $consultation): RedirectResponse
    {
        $this->authorizeConsultation($consultation);

        $validated = $request->validate([
            'thread_id' => ['required', 'integer', 'exists:message_threads,id'],
        ]);

        $thread = MessageThread::findOrFail($validated['thread_id']);
        if ($thread->client_id !== $consultation->client_id) {
            abort(404, __('Not found.'));
        }

        $thread->update(['consultation_id' => $consultation->id]);

        return redirect()
            ->route('admin.core.consultations.show', $consultation)
            ->with('success', __('Message thread linked successfully.'));
    }

    /**
     * Unlink message thread from consultation.
     */
    public function unlinkThread(Consultation $consultation, MessageThread $thread): RedirectResponse
    {
        $this->authorizeConsultation($consultation);
        if ($thread->consultation_id !== $consultation->id) {
            abort(404, __('Not found.'));
        }

        $thread->update(['consultation_id' => null]);

        return redirect()
            ->route('admin.core.consultations.show', $consultation)
            ->with('success', __('Message thread unlinked.'));
    }

    /**
     * Create new message thread for this consultation.
     */
    public function createThread(Request $request, Consultation $consultation): RedirectResponse
    {
        $this->authorizeConsultation($consultation);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
        ]);

        $thread = $consultation->client->messageThreads()->create([
            'client_id' => $consultation->client_id,
            'consultation_id' => $consultation->id,
            'subject' => $validated['subject'],
        ]);

        return redirect()
            ->route('admin.core.clients.threads.show', [$consultation->client, $thread])
            ->with('success', __('Message thread created for this consultation.'));
    }
}
