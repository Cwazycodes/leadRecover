<?php

namespace App\Tenancy;

use App\Models\Business;

/**
 * Holds the "current" business for the lifetime of a request / job.
 *
 * Bound as a singleton in the container. Middleware sets the tenant for
 * authenticated web traffic; webhooks and the admin area run without a
 * tenant set and query data with explicit business_id values instead.
 */
class Tenancy
{
    protected ?Business $business = null;

    public function set(?Business $business): static
    {
        $this->business = $business;

        return $this;
    }

    public function current(): ?Business
    {
        return $this->business;
    }

    public function check(): bool
    {
        return $this->business !== null;
    }

    public function id(): ?int
    {
        return $this->business?->id;
    }

    public function forget(): void
    {
        $this->business = null;
    }

    /**
     * Run a callback as a given tenant, restoring the previous tenant after.
     */
    public function run(Business $business, callable $callback): mixed
    {
        $previous = $this->business;
        $this->business = $business;

        try {
            return $callback($business);
        } finally {
            $this->business = $previous;
        }
    }
}
