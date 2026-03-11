<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ALOS-S1-15.2 — Generated Reports Storage.
 * Stores each generated report for in-app display and to avoid duplicate sends.
 */
class GeneratedReport extends Model
{
    public const TYPE_CASE_STATUS = 'case_status';
    public const TYPE_ACTIVITY_SUMMARY = 'activity_summary';
    public const TYPE_NEW_DOCUMENTS = 'new_documents';

    public const STATUS_GENERATED = 'generated';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'client_id',
        'report_type',
        'period_start',
        'period_end',
        'title',
        'payload_json',
        'status',
        'generated_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'payload_json' => 'array',
            'generated_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getPayload(): array
    {
        return is_array($this->payload_json) ? $this->payload_json : [];
    }

    public function markSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function markFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }
}
