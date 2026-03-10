<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ALOS-S1-13 — Log of sent session reminders (avoid duplicates).
 */
class SessionReminderLog extends Model
{
    protected $table = 'session_reminder_logs';

    public $timestamps = false;

    protected $fillable = [
        'case_session_id',
        'reminder_rule_id',
        'user_id',
        'recipient_type',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function caseSession(): BelongsTo
    {
        return $this->belongsTo(CaseSession::class, 'case_session_id');
    }

    public function reminderRule(): BelongsTo
    {
        return $this->belongsTo(ReminderRule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
