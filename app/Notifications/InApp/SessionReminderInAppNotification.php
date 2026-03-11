<?php

namespace App\Notifications\InApp;

use App\Models\CaseSession;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Support\Facades\App;

/**
 * ALOS-S1-26 — Session reminder in-app: notify case team or single user.
 */
class SessionReminderInAppNotification
{
    public static function send(CaseSession $session, string $reminderLabel = ''): void
    {
        $case = $session->case;
        $userIds = $case->client->teamAccess()->pluck('id')->toArray();
        foreach ($userIds as $uid) {
            self::sendToUser($session, $uid, $reminderLabel);
        }
    }

    public static function sendToUser(CaseSession $session, User|int $user, string $reminderLabel = ''): void
    {
        $u = $user instanceof User ? $user : User::find($user);
        if (! $u) {
            return;
        }
        $case = $session->case;
        $client = $case->client;
        $tenantId = $case->tenant_id;
        $date = $session->session_date->format('Y-m-d');
        $time = $session->session_time ? substr($session->session_time, 0, 5) : '';
        $title = __('Session reminder');
        $messageBody = __('You have a session for case :number on :date :time.', [
            'number' => $case->case_number,
            'date' => $date,
            'time' => $time ? ' ' . $time : '',
        ]);
        $data = [
            'link' => route('admin.core.cases.sessions.index', $case),
            'entity_type' => 'case_session',
            'entity_id' => $session->id,
            'case_id' => $case->id,
            'client_id' => $client->id,
        ];
        App::make(InAppNotificationService::class)->notify(
            $u->id,
            \App\Models\InAppNotification::TYPE_SESSION_REMINDER,
            $title,
            $messageBody,
            $tenantId,
            $data
        );
    }
}
