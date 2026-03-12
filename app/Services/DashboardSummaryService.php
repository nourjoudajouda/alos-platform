<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CaseModel;
use App\Models\CaseSession;
use App\Models\Client;
use App\Models\Consultation;
use App\Models\Document;
use App\Models\GeneratedReport;
use App\Models\InAppNotification;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * ALOS-S1-27 — Dashboard Summary Service.
 *
 * Gathers dashboard metrics and lists for the Internal Office Dashboard.
 * Respects tenant isolation and client team access (admin/managing_partner see full tenant; lawyer/assistant see only their clients).
 */
class DashboardSummaryService
{
    public function __construct(
        protected User $user
    ) {}

    /**
     * Get full dashboard data for the current user (tenant staff).
     */
    public function getSummary(): array
    {
        $tenantId = $this->user->tenant_id;
        if (! $tenantId) {
            return $this->emptySummary();
        }

        $clientIds = $this->accessibleClientIds();

        return [
            'metrics' => $this->metrics($clientIds, $tenantId),
            'upcoming_sessions' => $this->upcomingSessions($clientIds),
            'recent_messages' => $this->recentMessages($clientIds),
            'recent_documents' => $this->recentDocuments($clientIds),
            'recent_activity' => $this->recentActivity($clientIds, $tenantId),
        ];
    }

    /**
     * Client IDs the user is allowed to see (tenant scope + team access).
     */
    protected function accessibleClientIds(): array
    {
        if ($this->user->hasRole('admin') || $this->user->hasRole('managing_partner')) {
            return Client::where('tenant_id', $this->user->tenant_id)->pluck('id')->all();
        }
        return $this->user->clientAccess()->pluck('clients.id')->all();
    }

    protected function emptySummary(): array
    {
        return [
            'metrics' => [
                'total_clients' => 0,
                'total_cases' => 0,
                'open_cases' => 0,
                'closed_cases' => 0,
                'consultations_count' => 0,
                'documents_count' => 0,
                'message_threads_count' => 0,
                'unread_messages_approx' => 0,
                'upcoming_sessions_count' => 0,
                'reports_pending_count' => 0,
                'unread_notifications_count' => 0,
            ],
            'upcoming_sessions' => [],
            'recent_messages' => [],
            'recent_documents' => [],
            'recent_activity' => [],
        ];
    }

    protected function metrics(array $clientIds, int $tenantId): array
    {
        if (empty($clientIds)) {
            $base = $this->emptySummary()['metrics'];
            $base['unread_notifications_count'] = InAppNotification::forTenant($tenantId)->forUser($this->user->id)->unread()->count();
            return $base;
        }

        $casesQuery = CaseModel::where('tenant_id', $tenantId)->whereIn('client_id', $clientIds);
        $totalCases = (clone $casesQuery)->count();
        $openCases = (clone $casesQuery)->where('status', CaseModel::STATUS_OPEN)->count();
        $closedCases = (clone $casesQuery)->where('status', CaseModel::STATUS_CLOSED)->count();

        $sessionsQuery = CaseSession::query()
            ->whereHas('case', fn ($q) => $q->where('tenant_id', $tenantId)->whereIn('client_id', $clientIds))
            ->where('status', CaseSession::STATUS_SCHEDULED)
            ->where('session_date', '>=', now()->startOfDay());
        $upcomingSessionsCount = (clone $sessionsQuery)->count();

        $threadIds = MessageThread::whereIn('client_id', $clientIds)->pluck('id')->all();
        $messageThreadsCount = count($threadIds);
        $unreadApprox = 0;
        if ($threadIds) {
            $unreadApprox = Message::whereIn('message_thread_id', $threadIds)
                ->where('user_id', '!=', $this->user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();
        }

        return [
            'total_clients' => count($clientIds),
            'total_cases' => $totalCases,
            'open_cases' => $openCases,
            'closed_cases' => $closedCases,
            'consultations_count' => Consultation::where('tenant_id', $tenantId)->whereIn('client_id', $clientIds)->count(),
            'documents_count' => Document::where('tenant_id', $tenantId)->whereIn('client_id', $clientIds)->count(),
            'message_threads_count' => $messageThreadsCount,
            'unread_messages_approx' => $unreadApprox,
            'upcoming_sessions_count' => $upcomingSessionsCount,
            'reports_pending_count' => GeneratedReport::where('tenant_id', $tenantId)->whereIn('client_id', $clientIds)->where('status', GeneratedReport::STATUS_GENERATED)->count(),
            'unread_notifications_count' => InAppNotification::forTenant($tenantId)->forUser($this->user->id)->unread()->count(),
        ];
    }

    protected function upcomingSessions(array $clientIds, int $limit = 10): array
    {
        if (empty($clientIds)) {
            return [];
        }

        return CaseSession::query()
            ->with(['case.client'])
            ->whereHas('case', fn ($q) => $q->whereIn('client_id', $clientIds))
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
                'client_name' => $s->case?->client?->name,
                'case_number' => $s->case?->case_number,
                'case_id' => $s->case_id,
            ])
            ->all();
    }

    protected function recentMessages(array $clientIds, int $limit = 8): array
    {
        if (empty($clientIds)) {
            return [];
        }

        $threadIds = MessageThread::whereIn('client_id', $clientIds)->pluck('id')->all();
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
            ->with(['client', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy('id');
        $threads = collect($orderedIds)->map(fn ($id) => $threadsById->get($id))->filter()->values();

        return $threads->map(function (MessageThread $t) {
            $last = $t->messages->first();
            return [
                'id' => $t->id,
                'client_name' => $t->client?->name,
                'client_id' => $t->client_id,
                'subject' => $t->subject,
                'last_message_body' => $last ? \Str::limit(strip_tags($last->body), 60) : null,
                'last_message_at' => $last?->created_at?->diffForHumans(),
                'last_message_at_raw' => $last?->created_at?->toIso8601String(),
            ];
        })->all();
    }

    protected function recentDocuments(array $clientIds, int $limit = 8): array
    {
        if (empty($clientIds)) {
            return [];
        }

        return Document::query()
            ->with('client')
            ->whereIn('client_id', $clientIds)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Document $d) => [
                'id' => $d->id,
                'name' => $d->name,
                'client_name' => $d->client?->name,
                'client_id' => $d->client_id,
                'created_at' => $d->created_at?->format('Y-m-d H:i'),
                'created_at_human' => $d->created_at?->diffForHumans(),
            ])
            ->all();
    }

    protected function recentActivity(array $clientIds, int $tenantId, int $limit = 15): array
    {
        $entityTypes = [
            AuditLog::ENTITY_CLIENT,
            AuditLog::ENTITY_CASE,
            AuditLog::ENTITY_CONSULTATION,
            AuditLog::ENTITY_DOCUMENT,
            AuditLog::ENTITY_MESSAGE,
            AuditLog::ENTITY_MESSAGE_THREAD,
            AuditLog::ENTITY_CASE_SESSION,
            AuditLog::ENTITY_REPORT,
        ];

        $query = AuditLog::query()
            ->with('user')
            ->where('tenant_id', $tenantId)
            ->whereIn('action', [
                AuditLog::ACTION_CREATE,
                AuditLog::ACTION_UPDATE,
                AuditLog::ACTION_CREATE_CLIENT,
                AuditLog::ACTION_UPDATE_CLIENT,
                AuditLog::ACTION_CREATE_CASE,
                AuditLog::ACTION_UPDATE_CASE,
                AuditLog::ACTION_CREATE_CONSULTATION,
                AuditLog::ACTION_UPDATE_CONSULTATION,
                AuditLog::ACTION_UPLOAD_DOCUMENT,
                AuditLog::ACTION_SHARE_DOCUMENT,
                AuditLog::ACTION_SEND_MESSAGE,
                AuditLog::ACTION_CREATE_SESSION,
                AuditLog::ACTION_UPDATE_SESSION,
                AuditLog::ACTION_GENERATE_REPORT,
            ])
            ->orderByDesc('created_at')
            ->limit($limit * 2);

        $logs = $query->get();
        $filtered = $logs->filter(function (AuditLog $log) use ($clientIds) {
            if (in_array($log->entity_type, [AuditLog::ENTITY_CLIENT], true)) {
                return in_array((int) $log->entity_id, $clientIds, true);
            }
            if (in_array($log->entity_type, [AuditLog::ENTITY_CASE, AuditLog::ENTITY_CONSULTATION, AuditLog::ENTITY_DOCUMENT, AuditLog::ENTITY_MESSAGE_THREAD, AuditLog::ENTITY_REPORT], true)) {
                $entity = $this->resolveEntityForClientFilter($log->entity_type, (int) $log->entity_id);
                return $entity !== null && in_array($entity, $clientIds, true);
            }
            if ($log->entity_type === AuditLog::ENTITY_MESSAGE) {
                $threadId = $this->getMessageThreadId($log->entity_id);
                if ($threadId === null) {
                    return false;
                }
                $clientId = MessageThread::where('id', $threadId)->value('client_id');
                return $clientId !== null && in_array((int) $clientId, $clientIds, true);
            }
            if ($log->entity_type === AuditLog::ENTITY_CASE_SESSION) {
                $caseId = CaseSession::where('id', $log->entity_id)->value('case_id');
                if ($caseId === null) {
                    return false;
                }
                $clientId = CaseModel::where('id', $caseId)->value('client_id');
                return $clientId !== null && in_array((int) $clientId, $clientIds, true);
            }
            return false;
        })->take($limit)->values();

        return $filtered->map(fn (AuditLog $log) => [
            'id' => $log->id,
            'action' => $log->action,
            'entity_type' => $log->entity_type,
            'entity_id' => $log->entity_id,
            'user_name' => $log->user?->name,
            'created_at' => $log->created_at?->toIso8601String(),
            'created_at_human' => $log->created_at?->diffForHumans(),
            'description' => $this->activityDescription($log),
        ])->all();
    }

    private function resolveEntityForClientFilter(string $entityType, int $entityId): ?int
    {
        return match ($entityType) {
            AuditLog::ENTITY_CLIENT => $entityId,
            AuditLog::ENTITY_CASE => CaseModel::where('id', $entityId)->value('client_id'),
            AuditLog::ENTITY_CONSULTATION => Consultation::where('id', $entityId)->value('client_id'),
            AuditLog::ENTITY_DOCUMENT => Document::where('id', $entityId)->value('client_id'),
            AuditLog::ENTITY_MESSAGE_THREAD => MessageThread::where('id', $entityId)->value('client_id'),
            AuditLog::ENTITY_REPORT => GeneratedReport::where('id', $entityId)->value('client_id'),
            default => null,
        };
    }

    private function getMessageThreadId(int $messageId): ?int
    {
        return Message::where('id', $messageId)->value('message_thread_id');
    }

    private function activityDescription(AuditLog $log): string
    {
        $action = $log->action;
        $entity = $log->entity_type;
        $key = "dashboard.activity.{$entity}.{$action}";
        $fallback = ucfirst(str_replace('_', ' ', $action)) . ' ' . str_replace('_', ' ', $entity);
        return __($key) !== $key ? __($key) : $fallback;
    }
}
