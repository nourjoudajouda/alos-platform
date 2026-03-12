<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ALOS-S1-29 — Subscription Plan (SUBSCRIPTIONPLAN schema: plan_name, price, user_limit, lawyer_limit, storage_limit, features_json).
 */
class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'plan_name',
        'price',
        'user_limit',
        'lawyer_limit',
        'storage_limit',
        'features_json',
    ];

    protected $attributes = [
        'features_json' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'user_limit' => 'integer',
            'lawyer_limit' => 'integer',
            'storage_limit' => 'integer',
            'features_json' => 'array',
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'subscription_plan_id');
    }
}
