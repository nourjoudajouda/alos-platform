<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ALOS-S1-13 — Reminder rules for session reminders.
 */
class ReminderRule extends Model
{
    protected $fillable = [
        'tenant_id',
        'label',
        'trigger_minutes',
        'channel_database',
        'channel_mail',
        'notify_client',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'channel_database' => 'boolean',
            'channel_mail' => 'boolean',
            'notify_client' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(SessionReminderLog::class, 'reminder_rule_id');
    }
}
