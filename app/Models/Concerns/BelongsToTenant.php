<?php

namespace App\Models\Concerns;

use App\Models\Business;
use App\Tenancy\Tenancy;
use App\Tenancy\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applied to any model that belongs to a single business (Lead, Call,
 * Interaction…). Provides:
 *
 *  - a global TenantScope so reads never leak across tenants, and
 *  - auto-population of business_id on create from the active tenant.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $tenancy = app(Tenancy::class);

            if (empty($model->business_id) && $tenancy->check()) {
                $model->business_id = $tenancy->id();
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
