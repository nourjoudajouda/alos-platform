<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ALOS-S1-09 — Single message in a thread. Sender is User (office or portal user).
 */
class Message extends Model
{
    protected $fillable = [
        'message_thread_id',
        'user_id',
        'body',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'message_thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /** Whether sender is client portal user (vs office). */
    public function isFromClient(): bool
    {
        return $this->user && $this->user->isClientPortalUser();
    }
}
