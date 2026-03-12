<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ALOS-S1-01 — Multi-Tenant SaaS: Tenant entity.
 */
class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subdomain',
        'username',
        'domain',
        'plan',
        'email',
        'managing_partner',
        'subscription_plan_id',
        'status',
        'subscription_status',
        'user_limit',
        'lawyer_limit',
        'storage_limit',
        'start_date',
        'end_date',
        'contract_start_date',
        'contract_end_date',
        'billing_cycle',
        'plan_price',
        'is_active',
        'public_site_enabled',
        'logo',
        'description',
        'phone',
        'city',
        'country',
    ];

    /** ALOS-S1-33 — Law firm status (platform admin). */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_INACTIVE = 'inactive';

    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_SUSPENDED, self::STATUS_INACTIVE];

    /** ALOS-S1-35 — Subscription lifecycle (contract/subscription status). */
    public const SUBSCRIPTION_STATUS_ACTIVE = 'active';
    public const SUBSCRIPTION_STATUS_TRIAL = 'trial';
    public const SUBSCRIPTION_STATUS_EXPIRED = 'expired';
    public const SUBSCRIPTION_STATUS_SUSPENDED = 'suspended';

    public const SUBSCRIPTION_STATUSES = [
        self::SUBSCRIPTION_STATUS_ACTIVE,
        self::SUBSCRIPTION_STATUS_TRIAL,
        self::SUBSCRIPTION_STATUS_EXPIRED,
        self::SUBSCRIPTION_STATUS_SUSPENDED,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'public_site_enabled' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'plan_price' => 'decimal:2',
    ];

    /** ALOS-S1-29 — Subscription plan (plan limits). */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /** Whether the public site (/f/{slug}) is enabled for this tenant. */
    public function hasPublicSiteEnabled(): bool
    {
        return (bool) ($this->public_site_enabled ?? true);
    }

    /** Plans available for filter (and for future use). */
    public const PLANS = ['free', 'starter', 'professional'];

    /** Whether this tenant is active and its users can log in from /login. */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Users belonging to this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Clients belonging to this tenant (ALOS-S1-06).
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Branding settings (ALOS-S1-21).
     */
    public function settings(): HasOne
    {
        return $this->hasOne(TenantSettings::class);
    }

    /** Get branding settings (create if not exists) */
    public function getSettingsOrCreate(): TenantSettings
    {
        return $this->settings()->firstOrCreate(
            ['tenant_id' => $this->id],
            [
                'display_name' => $this->name,
                'public_site_enabled' => $this->public_site_enabled ?? true,
            ]
        );
    }
}
