<?php

namespace App\Http\Middleware;

use App\Tenancy\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates premium features behind an active subscription or live trial.
 * Applied to feature routes (leads, settings) but NOT to the dashboard or
 * billing pages, so a lapsed customer can always get back to paying.
 */
class EnsureSubscribed
{
    public function __construct(protected Tenancy $tenancy)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $business = $this->tenancy->current();

        if ($business && ! $business->hasActivePlan()) {
            return redirect()->route('billing.index')
                ->with('warning', 'Choose a plan to keep recovering missed calls.');
        }

        return $next($request);
    }
}
