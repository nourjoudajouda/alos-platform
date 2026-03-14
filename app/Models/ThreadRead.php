<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ALOS-S1-09 — When a user last read a message thread (for unread indicators).
 */
class ThreadRead extends Model
{
    protected $fillable = [
        'message_thread_id',
        'user_id',
        'last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
        ];
    }

    public function messageThread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
