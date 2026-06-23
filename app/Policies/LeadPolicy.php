<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

/**
 * Tenant isolation is already enforced by the global TenantScope (a lead from
 * another business can't even be route-model-bound). This policy is a second
 * layer that asserts ownership explicitly.
 */
class LeadPolicy
{
    public function view(User $user, Lead $lead): bool
    {
        return $user->business_id === $lead->business_id;
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->business_id === $lead->business_id;
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->business_id === $lead->business_id && $user->canManageBusiness();
    }
}
