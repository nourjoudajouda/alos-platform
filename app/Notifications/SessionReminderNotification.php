<?php

namespace App\Notifications;

use App\Models\CaseSession;
use App\Models\ReminderRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ALOS-S1-13 — Session reminder (in-app + email).
 */
class SessionReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CaseSession $session,
        public ReminderRule $rule
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        // In-app uses ALOS-S1-26 notifications table; only mail here
        if ($this->rule->channel_mail && $notifiable->email) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $case = $this->session->case;
        $date = $this->session->session_date->format('Y-m-d');
        $time = $this->session->session_time ? substr($this->session->session_time, 0, 5) : '';
        $court = $this->session->court_name ?? '-';
        $location = $this->session->location ?? '';
        $url = url('/core/cases/' . $case->id);

        $subject = __('Session reminder') . ': ' . $case->case_number . ' — ' . $date;

        $message = (new MailMessage)
            ->subject($subject)
            ->line(__('You have an upcoming court session.'))
            ->line('**' . __('Case') . ':** ' . $case->case_number)
            ->line('**' . __('Date') . ':** ' . $date . ($time ? ' ' . $time : ''))
            ->line('**' . __('Court') . ':** ' . $court)
            ->when($location, fn ($m) => $m->line('**' . __('Location') . ':** ' . $location))
            ->action(__('View case'), $url);

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $case = $this->session->case;
        $date = $this->session->session_date->format('Y-m-d');
        $time = $this->session->session_time ? substr($this->session->session_time, 0, 5) : '';

        return [
            'type' => 'session_reminder',
            'case_session_id' => $this->session->id,
            'case_id' => $case->id,
            'case_number' => $case->case_number,
            'session_date' => $date,
            'session_time' => $time,
            'court_name' => $this->session->court_name,
            'location' => $this->session->location,
            'rule_label' => $this->rule->label,
            'message' => __('Session reminder') . ': ' . $case->case_number . ' — ' . $date . ($time ? ' ' . $time : ''),
        ];
    }
}
