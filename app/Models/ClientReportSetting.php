<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ALOS-S1-15.1 — Client Report Settings.
 * Per-client preferences for auto reports: types, delivery channel, frequency, recipients.
 */
class ClientReportSetting extends Model
{
    public const DELIVERY_IN_APP = 'in_app';
    public const DELIVERY_EMAIL = 'email';
    public const DELIVERY_BOTH = 'both';

    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_MAJOR_UPDATE = 'major_update';

    protected $fillable = [
        'tenant_id',
        'client_id',
        'case_status_enabled',
        'activity_summary_enabled',
        'new_documents_enabled',
        'delivery_channel',
        'frequency',
        'send_to_client',
        'send_to_responsible_lawyer',
        'send_to_office_management',
    ];

    protected function casts(): array
    {
        return [
            'case_status_enabled' => 'boolean',
            'activity_summary_enabled' => 'boolean',
            'new_documents_enabled' => 'boolean',
            'send_to_client' => 'boolean',
            'send_to_responsible_lawyer' => 'boolean',
            'send_to_office_management' => 'boolean',
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

    public function reportsEnabled(): bool
    {
        return $this->case_status_enabled || $this->activity_summary_enabled || $this->new_documents_enabled;
    }

    public function shouldSendInApp(): bool
    {
        return $this->delivery_channel === self::DELIVERY_IN_APP || $this->delivery_channel === self::DELIVERY_BOTH;
    }

    public function shouldSendEmail(): bool
    {
        return $this->delivery_channel === self::DELIVERY_EMAIL || $this->delivery_channel === self::DELIVERY_BOTH;
    }
}
