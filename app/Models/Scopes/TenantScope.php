<?php

namespace App\Models\Scopes;

use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * ALOS-S1-02 — Global scope: restrict queries to the current tenant.
 * When a current tenant is set (from TenantContext), only rows with that tenant_id are returned.
 * When no tenant context (e.g. platform admin), the scope is not applied so all rows are visible.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenant = TenantContext::currentTenant();

        if ($tenant instanceof Tenant) {
            $builder->where($model->getTable() . '.tenant_id', $tenant->id);
        }
    }
}
