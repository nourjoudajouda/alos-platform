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
}
