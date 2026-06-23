<?php

namespace App\Http\Middleware;

use App\Tenancy\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active business for the authenticated user and activates the
 * tenant scope for the rest of the request. Every tenant-facing route group
 * runs through this so a user can only ever touch their own data.
 */
class IdentifyTenant
{
    public function __construct(protected Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->business) {
            // Platform owners manage the SaaS, not a single business.
            if ($user->isPlatformAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            abort(403, 'No business is associated with your account.');
        }

        $this->tenancy->set($user->business);
        view()->share('currentBusiness', $user->business);

        return $next($request);
    }
}
