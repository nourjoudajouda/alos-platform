<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\CaseSession;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Document;
use App\Models\GeneratedReport;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * ALOS-S1-28 — Client Portal Dashboard Service.
 *
 * Gathers dashboard data for a single client (portal user). All data is scoped to:
 * - client_id of the current user's client
 * - visibility / sharing rules (shared documents only, shared consultations only)
 * No internal notes or other clients' data are exposed.
 */
class ClientDashboardService
{
    public function __construct(
        protected User $user
    ) {}

    /**
     * Get full dashboard data for the portal client. User must have client_id.
     */
    public function getSummary(): array
    {
        $client = $this->user->client;
        if (! $client instanceof Client) {
            return $this->emptySummary();
        }

        $clientId = (int) $client->id;

        return [
            'metrics' => $this->metrics($clientId),
            'my_cases' => $this->myCases($clientId),
            'recent_messages' => $this->recentMessages($clientId),
            'shared_documents' => $this->sharedDocuments($clientId),
            'upcoming_sessions' => $this->upcomingSessions($clientId),
            'recent_reports' => $this->recentReports($clientId),
        ];
    }

    protected function emptySummary(): array
    {
        return [
            'metrics' => [
                'cases_count' => 0,
                'open_cases_count' => 0,
                'consultations_count' => 0,
                'shared_documents_count' => 0,
                'message_threads_count' => 0,
                'unread_messages_approx' => 0,
                'reports_count' => 0,
                'upcoming_sessions_count' => 0,
            ],
            'my_cases' => [],
            'recent_messages' => [],
            'shared_documents' => [],
            'upcoming_sessions' => [],
            'recent_reports' => [],
        ];
    }

    protected function metrics(int $clientId): array
    {
        $casesQuery = CaseModel::where('client_id', $clientId);
        $casesCount = (clone $casesQuery)->count();
        $openCasesCount = (clone $casesQuery)->where('status', CaseModel::STATUS_OPEN)->count();

        $consultationsCount = Consultation::where('client_id', $clientId)
            ->where('is_shared_with_client', true)
            ->count();

        $sharedDocumentsCount = Document::where('client_id', $clientId)
            ->where('visibility', Document::VISIBILITY_SHARED)
            ->count();

        $threadIds = MessageThread::where('client_id', $clientId)->pluck('id')->all();
        $messageThreadsCount = count($threadIds);
        $unreadApprox = 0;
        if ($threadIds) {
            $unreadApprox = Message::whereIn('message_thread_id', $threadIds)
                ->where('user_id', '!=', $this->user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
        }

        $reportsCount = GeneratedReport::where('client_id', $clientId)->count();

        $upcomingSessionsCount = CaseSession::query()
            ->whereHas('case', fn ($q) => $q->where('client_id', $clientId))
            ->where('status', CaseSession::STATUS_SCHEDULED)
            ->where('session_date', '>=', now()->startOfDay())
            ->count();

        return [
            'cases_count' => $casesCount,
            'open_cases_count' => $openCasesCount,
            'consultations_count' => $consultationsCount,
            'shared_documents_count' => $sharedDocumentsCount,
            'message_threads_count' => $messageThreadsCount,
            'unread_messages_approx' => $unreadApprox,
            'reports_count' => $reportsCount,
            'upcoming_sessions_count' => $upcomingSessionsCount,
        ];
    }

    /** My Cases — client's cases only (case number, type, status, last update). */
    protected function myCases(int $clientId, int $limit = 8): array
    {
        return CaseModel::query()
            ->where('client_id', $clientId)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (CaseModel $c) => [
                'id' => $c->id,
                'case_number' => $c->case_number,
                'case_type' => $c->case_type,
                'status' => $c->status,
                'updated_at' => $c->updated_at?->format('Y-m-d H:i'),
                'updated_at_human' => $c->updated_at?->diffForHumans(),
            ])
            ->all();
    }

    /** Recent Messages — threads for this client; last message and time. */
    protected function recentMessages(int $clientId, int $limit = 6): array
    {
        $threadIds = MessageThread::where('client_id', $clientId)->pluck('id')->all();
        if (empty($threadIds)) {
            return [];
        }

        $latestPerThread = Message::query()
            ->select('message_thread_id', DB::raw('MAX(created_at) as last_at'))
            ->whereIn('message_thread_id', $threadIds)
            ->groupBy('message_thread_id')
            ->orderByDesc('last_at')
            ->limit($limit)
            ->pluck('last_at', 'message_thread_id');
        $orderedIds = $latestPerThread->keys()->all();
        if (empty($orderedIds)) {
            return [];
        }

        $threadsById = MessageThread::query()
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy('id');
        $threads = collect($orderedIds)->map(fn ($id) => $threadsById->get($id))->filter()->values();

        return $threads->map(function (MessageThread $t) {
            $last = $t->messages->first();
            $fromOffice = $last && $last->user_id !== $this->user->id;
            return [
                'id' => $t->id,
                'subject' => $t->subject,
                'last_message_body' => $last ? \Str::limit(strip_tags($last->body), 50) : null,
                'last_message_at' => $last?->created_at?->diffForHumans(),
                'last_message_at_raw' => $last?->created_at?->toIso8601String(),
                'has_new_from_office' => $fromOffice,
            ];
        })->all();
    }

    /** Shared Documents — visibility = shared only. */
    protected function sharedDocuments(int $clientId, int $limit = 6): array
    {
        return Document::query()
            ->where('client_id', $clientId)
            ->where('visibility', Document::VISIBILITY_SHARED)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (Document $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'mime_type' => $d->mime_type,
                'updated_at' => $d->updated_at?->format('Y-m-d'),
                'updated_at_human' => $d->updated_at?->diffForHumans(),
            ])
            ->all();
    }

    /** Upcoming Sessions — scheduled sessions for this client's cases. */
    protected function upcomingSessions(int $clientId, int $limit = 6): array
    {
        return CaseSession::query()
            ->with(['case'])
            ->whereHas('case', fn ($q) => $q->where('client_id', $clientId))
            ->where('status', CaseSession::STATUS_SCHEDULED)
            ->where('session_date', '>=', now()->startOfDay())
            ->orderBy('session_date')
            ->orderBy('session_time')
            ->limit($limit)
            ->get()
            ->map(fn (CaseSession $s) => [
                'id' => $s->id,
                'session_date' => $s->session_date?->format('Y-m-d'),
                'session_date_formatted' => $s->session_date?->translatedFormat('d M Y'),
                'session_time' => $s->session_time,
                'court_name' => $s->court_name,
                'case_number' => $s->case?->case_number,
            ])
            ->all();
    }

    /** Recent Reports — generated reports for this client. */
    protected function recentReports(int $clientId, int $limit = 5): array
    {
        return GeneratedReport::query()
            ->where('client_id', $clientId)
            ->orderByDesc('generated_at')
            ->limit($limit)
            ->get()
            ->map(fn (GeneratedReport $r) => [
                'id' => $r->id,
                'title' => $r->title,
                'report_type' => $r->report_type,
                'generated_at' => $r->generated_at?->format('Y-m-d'),
                'generated_at_human' => $r->generated_at?->diffForHumans(),
            ])
            ->all();
    }
}
