<?php

namespace App\Jobs;

use App\Models\CaseSession;
use App\Models\ReminderRule;
use App\Models\SessionReminderLog;
use App\Models\User;
use App\Notifications\InApp\SessionReminderInAppNotification;
use App\Notifications\SessionReminderNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ALOS-S1-13 — Send session reminders to lawyer, team, optionally client.
 */
class SendSessionRemindersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now();
        $rules = ReminderRule::where('active', true)->orderBy('sort_order')->get();
        if ($rules->isEmpty()) {
            return;
        }

        $sessions = CaseSession::query()
            ->where('status', CaseSession::STATUS_SCHEDULED)
            ->with(['case.client.teamAccess', 'case.client.portalUser', 'case.responsibleLawyer', 'assignedUser'])
            ->get();

        foreach ($sessions as $session) {
            $sessionDatetime = $this->getSessionDatetime($session);
            if (! $sessionDatetime->isFuture()) {
                continue;
            }

            $minutesUntil = (int) round($now->diffInMinutes($sessionDatetime, false)); // positive = session in future

            foreach ($rules as $rule) {
                if ($minutesUntil > $rule->trigger_minutes) {
                    continue;
                }

                $recipients = $this->getRecipients($session, $rule);
                foreach ($recipients as $user) {
                    if (! $user || ! $user->exists) {
                        continue;
                    }
                    $this->sendToUser($session, $rule, $user, 'team');
                }

                if ($rule->notify_client) {
                    $portalUser = $session->case->client->portalUser;
                    if ($portalUser) {
                        $this->sendToUser($session, $rule, $portalUser, 'client');
                    }
                }
            }
        }
    }

    private function getSessionDatetime(CaseSession $session): Carbon
    {
        $date = $session->session_date;
        $time = $session->session_time
            ? Carbon::parse($session->session_time)->format('H:i:s')
            : '09:00:00';
        return Carbon::parse($date->format('Y-m-d') . ' ' . $time);
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function getRecipients(CaseSession $session, ReminderRule $rule): \Illuminate\Support\Collection
    {
        $case = $session->case;
        $client = $case->client;
        $users = collect();

        $responsibleLawyer = $case->responsibleLawyer;
        if ($responsibleLawyer && ! $responsibleLawyer->isClientPortalUser()) {
            $users->push($responsibleLawyer);
        }

        $assignedUser = $session->assignedUser;
        if ($assignedUser && ! $assignedUser->isClientPortalUser() && ! $users->contains('id', $assignedUser->id)) {
            $users->push($assignedUser);
        }

        foreach ($client->teamAccess as $user) {
            if (! $user->isClientPortalUser() && ! $users->contains('id', $user->id)) {
                $users->push($user);
            }
        }

        return $users->unique('id');
    }

    private function sendToUser(CaseSession $session, ReminderRule $rule, User $user, string $recipientType): void
    {
        if ($this->alreadySent($session, $rule, $user)) {
            return;
        }

        if (! $rule->channel_database && ! $rule->channel_mail) {
            return;
        }

        if ($rule->channel_database) {
            SessionReminderInAppNotification::sendToUser($session, $user, $rule->label ?? '');
        }
        if ($rule->channel_mail) {
            $user->notify(new SessionReminderNotification($session, $rule));
        }
        $this->logSent($session, $rule, $user, $recipientType);
    }

    private function alreadySent(CaseSession $session, ReminderRule $rule, User $user): bool
    {
        return SessionReminderLog::where('case_session_id', $session->id)
            ->where('reminder_rule_id', $rule->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    private function logSent(CaseSession $session, ReminderRule $rule, User $user, string $recipientType): void
    {
        SessionReminderLog::create([
            'case_session_id' => $session->id,
            'reminder_rule_id' => $rule->id,
            'user_id' => $user->id,
            'recipient_type' => $recipientType,
            'sent_at' => now(),
        ]);
    }
}
