<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ALOS-S1-25 — Compliance Log: access violations, unauthorized attempts, failed logins.
 */
class ComplianceLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'user_type',
        'attempted_action',
        'target_entity',
        'target_id',
        'description',
        'ip_address',
        'user_agent',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
