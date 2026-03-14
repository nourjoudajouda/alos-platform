<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ALOS-S1-09 — Secure Messaging: conversation thread between client and office.
 */
class MessageThread extends Model
{
    protected $fillable = [
        'client_id',
        'case_id',
        'consultation_id',
        'subject',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /** Latest message for preview / last activity. */
    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latestOfMany('created_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function threadReads(): HasMany
    {
        return $this->hasMany(ThreadRead::class);
    }

    /** Mark thread as read for a user (when they open the thread). */
    public function markAsReadBy(int $userId): void
    {
        ThreadRead::updateOrCreate(
            [
                'message_thread_id' => $this->id,
                'user_id' => $userId,
            ],
            ['last_read_at' => now()]
        );
    }

    /** Count unread messages for a user (messages from others after their last read). */
    public function unreadCountFor(int $userId): int
    {
        $read = $this->threadReads()->where('user_id', $userId)->first();
        $cutoff = $read?->last_read_at ?? $this->created_at;

        return $this->messages()
            ->where('created_at', '>', $cutoff)
            ->where('user_id', '!=', $userId)
            ->count();
    }
}
