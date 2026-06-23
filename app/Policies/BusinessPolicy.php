<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    /**
     * Only owners/admins of the business may change settings or billing.
     */
    public function manage(User $user, Business $business): bool
    {
        return $user->business_id === $business->id && $user->canManageBusiness();
    }
}
