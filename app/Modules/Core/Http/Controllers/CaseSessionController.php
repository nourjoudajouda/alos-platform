<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use App\Models\CaseSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ALOS-S1-12 — Case Sessions & Calendar (Court Hearings).
 */
class CaseSessionController extends Controller
{
    private function authorizeCase(CaseModel $case): void
    {
        $user = auth()->user();
        if ($user->isClientPortalUser()) {
            abort(403, __('Access denied.'));
        }
        if (! $case->client->teamAccess()->where('user_id', $user->id)->exists()) {
            abort(404, __('Not found.'));
        }
    }

    public function index(Request $request, CaseModel $case): View
    {
        $this->authorizeCase($case);

        $sessions = $case->sessions()
            ->with('assignedUser')
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->paginate(15)
            ->withQueryString();

        $assignableUsers = User::whereNull('client_id')->orderBy('name')->get();
        if (auth()->user()->tenant_id) {
            $assignableUsers = User::where('tenant_id', auth()->user()->tenant_id)->whereNull('client_id')->orderBy('name')->get();
        }

        return view('core::content.cases.sessions.index', [
            'case' => $case,
            'sessions' => $sessions,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    public function create(CaseModel $case): View
    {
        $this->authorizeCase($case);

        $assignableUsers = User::whereNull('client_id')->orderBy('name')->get();
        if (auth()->user()->tenant_id) {
            $assignableUsers = User::where('tenant_id', auth()->user()->tenant_id)->whereNull('client_id')->orderBy('name')->get();
        }

        return view('core::content.cases.sessions.create', [
            'case' => $case,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    public function store(Request $request, CaseModel $case): RedirectResponse
    {
        $this->authorizeCase($case);

        $validated = $request->validate([
            'session_date' => ['required', 'date'],
            'session_time' => ['nullable', 'date_format:H:i'],
            'court_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(array_keys(CaseSession::STATUSES))],
        ]);

        $case->sessions()->create($validated);

        return redirect()
            ->route('admin.core.cases.sessions.index', $case)
            ->with('success', __('Session created successfully.'));
    }

    public function edit(CaseModel $case, CaseSession $session): View
    {
        $this->authorizeCase($case);
        if ($session->case_id !== (int) $case->id) {
            abort(404);
        }

        $assignableUsers = User::whereNull('client_id')->orderBy('name')->get();
        if (auth()->user()->tenant_id) {
            $assignableUsers = User::where('tenant_id', auth()->user()->tenant_id)->whereNull('client_id')->orderBy('name')->get();
        }

        return view('core::content.cases.sessions.edit', [
            'case' => $case,
            'session' => $session,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    public function update(Request $request, CaseModel $case, CaseSession $session): RedirectResponse
    {
        $this->authorizeCase($case);
        if ($session->case_id !== (int) $case->id) {
            abort(404);
        }

        $validated = $request->validate([
            'session_date' => ['required', 'date'],
            'session_time' => ['nullable', 'date_format:H:i'],
            'court_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['required', Rule::in(array_keys(CaseSession::STATUSES))],
        ]);

        $session->update($validated);

        return redirect()
            ->route('admin.core.cases.sessions.index', $case)
            ->with('success', __('Session updated successfully.'));
    }

    public function destroy(CaseModel $case, CaseSession $session): RedirectResponse
    {
        $this->authorizeCase($case);
        if ($session->case_id !== (int) $case->id) {
            abort(404);
        }
        $session->delete();

        return redirect()
            ->route('admin.core.cases.sessions.index', $case)
            ->with('success', __('Session deleted.'));
    }

    /**
     * Calendar view for case sessions (court hearings).
     */
    public function calendar(Request $request, CaseModel $case): View
    {
        $this->authorizeCase($case);

        $month = $request->get('month', now()->format('Y-m'));
        $parsed = \DateTime::createFromFormat('Y-m', $month);
        $monthDate = $parsed ?: now();

        $sessions = $case->sessions()
            ->whereYear('session_date', $monthDate->format('Y'))
            ->whereMonth('session_date', $monthDate->format('m'))
            ->with('assignedUser')
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get();

        return view('core::content.cases.sessions.calendar', [
            'case' => $case,
            'sessions' => $sessions,
            'monthDate' => $monthDate,
        ]);
    }

    /**
     * JSON events for FullCalendar (optional).
     */
    public function events(CaseModel $case): JsonResponse
    {
        $this->authorizeCase($case);

        $events = $case->sessions()
            ->with('assignedUser')
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->get()
            ->map(function (CaseSession $s) {
                $start = $s->session_date->format('Y-m-d') . ($s->session_time ? ' ' . $s->session_time : ' 09:00:00');
                return [
                    'id' => $s->id,
                    'title' => ($s->court_name ?: $s->case->case_number) . ($s->session_time ? ' ' . substr($s->session_time, 0, 5) : ''),
                    'start' => $start,
                    'allDay' => ! $s->session_time,
                    'url' => route('admin.core.cases.sessions.edit', [$s->case, $s]),
                    'extendedProps' => [
                        'court' => $s->court_name,
                        'location' => $s->location,
                        'status' => $s->status,
                    ],
                ];
            });

        return response()->json($events);
    }
}
