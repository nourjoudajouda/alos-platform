<?php

namespace App\Services\Reports;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * ALOS-S1-15.4 — Activity Summary Report Generator.
 * Summary of activity in a period: updated cases, new/updated consultations, new messages, upcoming sessions.
 */
class ActivitySummaryReportGenerator
{
    public function generate(Client $client, Carbon $periodStart, Carbon $periodEnd): array
    {
        $periodEndEod = $periodEnd->copy()->endOfDay();

        $casesUpdated = $client->cases()
            ->whereBetween('updated_at', [$periodStart, $periodEndEod])
            ->with('responsibleLawyer')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($c) => [
                'case_id' => $c->id,
                'case_number' => $c->case_number,
                'case_type' => $c->case_type,
                'status' => $c->status,
                'updated_at' => $c->updated_at->toIso8601String(),
                'responsible_lawyer' => $c->responsibleLawyer?->name,
            ])
            ->values()
            ->all();

        $consultationsNewOrUpdated = $client->consultations()
            ->where(function ($q) use ($periodStart, $periodEndEod) {
                $q->whereBetween('created_at', [$periodStart, $periodEndEod])
                    ->orWhereBetween('updated_at', [$periodStart, $periodEndEod]);
            })
            ->with('responsibleUser')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($c) => [
                'consultation_id' => $c->id,
                'title' => $c->title,
                'consultation_date' => $c->consultation_date?->format('Y-m-d'),
                'status' => $c->status,
                'updated_at' => $c->updated_at->toIso8601String(),
                'responsible_user' => $c->responsibleUser?->name,
            ])
            ->values()
            ->all();

        $threadIds = $client->messageThreads()->pluck('id');
        $newMessagesCount = 0;
        if ($threadIds->isNotEmpty()) {
            $newMessagesCount = DB::table('messages')
                ->whereIn('message_thread_id', $threadIds)
                ->whereBetween('created_at', [$periodStart, $periodEndEod])
                ->count();
        }

        $upcomingSessions = [];
        foreach ($client->cases as $case) {
            foreach ($case->sessions()->where('status', 'scheduled')
                ->where('session_date', '>=', $periodStart->format('Y-m-d'))
                ->where('session_date', '<=', $periodEnd->format('Y-m-d'))
                ->orderBy('session_date')
                ->orderBy('session_time')
                ->get() as $session) {
                $upcomingSessions[] = [
                    'session_id' => $session->id,
                    'case_id' => $case->id,
                    'case_number' => $case->case_number,
                    'session_date' => $session->session_date?->format('Y-m-d'),
                    'session_time' => $session->session_time,
                    'court_name' => $session->court_name,
                    'location' => $session->location,
                ];
            }
        }

        $title = __('Activity Summary Report') . ' — ' . $client->name . ' — ' . $periodStart->format('Y-m-d') . ' / ' . $periodEnd->format('Y-m-d');

        return [
            'title' => $title,
            'report_type' => 'activity_summary',
            'client_id' => $client->id,
            'client_name' => $client->name,
            'generated_at' => now()->toIso8601String(),
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'cases_updated' => $casesUpdated,
            'consultations_new_or_updated' => $consultationsNewOrUpdated,
            'new_messages_count' => $newMessagesCount,
            'upcoming_sessions' => $upcomingSessions,
            'summary' => [
                'cases_updated_count' => count($casesUpdated),
                'consultations_count' => count($consultationsNewOrUpdated),
                'new_messages_count' => $newMessagesCount,
                'upcoming_sessions_count' => count($upcomingSessions),
            ],
        ];
    }
}
