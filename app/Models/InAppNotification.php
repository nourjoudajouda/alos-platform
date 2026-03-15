<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ALOS-S1-26 — In-App Notification (database).
 * Schema: tenant_id, user_id, type, title, message, read_status, read_at, data (link/entity), created_at.
 */
class InAppNotification extends Model
{
    public const UPDATED_AT = null;

    public const TYPE_NEW_MESSAGE = 'new_message';
    public const TYPE_SESSION_REMINDER = 'session_reminder';
    public const TYPE_DOCUMENT_SHARED = 'document_shared';
    public const TYPE_REPORT_GENERATED = 'report_generated';
    public const TYPE_CASE_UPDATED = 'case_updated';
    public const TYPE_CONSULTATION = 'consultation';

    protected $table = 'notifications';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'title',
        'message',
        'read_status',
        'read_at',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'read_status' => 'boolean',
            'read_at' => 'datetime',
            'data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTenant($query, ?int $tenantId)
    {
        if ($tenantId === null) {
            return $query->whereNull('tenant_id');
        }
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeUnread($query)
    {
        return $query->where('read_status', false);
    }

    public function markAsRead(): void
    {
        $this->update(['read_status' => true, 'read_at' => now()]);
    }

    public function getLinkAttribute(): ?string
    {
        return $this->data['link'] ?? null;
    }

    /**
     * Human-readable labels for notification types (for portal display).
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_NEW_MESSAGE => __('New message'),
            self::TYPE_SESSION_REMINDER => __('Upcoming session'),
            self::TYPE_DOCUMENT_SHARED => __('Document shared'),
            self::TYPE_REPORT_GENERATED => __('Report generated'),
            self::TYPE_CASE_UPDATED => __('Case status updated'),
            self::TYPE_CONSULTATION => __('Consultation'),
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }
}
