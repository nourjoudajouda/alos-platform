<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ALOS-S1-01 — Multi-Tenant SaaS: Tenant entity.
 */
class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'plan',
    ];

    /** Plans available for filter (and for future use). */
    public const PLANS = ['free', 'starter', 'professional'];

    /**
     * Users belonging to this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
