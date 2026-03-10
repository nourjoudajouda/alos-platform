<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ALOS-S1-14 — Consultations Management Module.
 * Consultations linked to clients; can be shared with client in portal.
 */
class Consultation extends Model
{
    protected $fillable = [
        'tenant_id',
        'client_id',
        'consultation_date',
        'responsible_user_id',
        'title',
        'summary',
        'internal_notes',
        'status',
        'is_shared_with_client',
    ];

    protected function casts(): array
    {
        return [
            'consultation_date' => 'date',
            'is_shared_with_client' => 'boolean',
        ];
    }

    public const STATUS_OPEN = 'open';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_OPEN => 'Open',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_ARCHIVED => 'Archived',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function messageThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class);
    }
}
