<?php

namespace App\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that constrains every query on a tenant-owned model to the
 * currently active business. Evaluated at query-build time (not model-boot
 * time) so switching tenants mid-process — common in queue workers and the
 * test suite — always filters correctly.
 *
 * When no tenant is active the scope is a no-op, allowing webhook handlers,
 * the platform-admin area and console commands to operate cross-tenant with
 * explicit business_id filters.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenancy = app(Tenancy::class);

        if ($tenancy->check()) {
            $builder->where($model->getTable().'.business_id', $tenancy->id());
        }
    }
}
