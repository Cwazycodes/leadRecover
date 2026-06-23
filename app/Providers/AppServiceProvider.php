<?php

namespace App\Providers;

use App\Models\Business;
use App\Repositories\Contracts\LeadRepositoryInterface;
use App\Repositories\LeadRepository;
use App\Tenancy\Tenancy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One Tenancy instance per request/worker lifecycle.
        $this->app->singleton(Tenancy::class);

        // Repository contract binding (swappable for tests / other stores).
        $this->app->bind(LeadRepositoryInterface::class, LeadRepository::class);

        // We wire events explicitly in EventServiceProvider, so turn off the
        // framework's auto-discovery to avoid registering listeners twice.
        \Illuminate\Foundation\Support\Providers\EventServiceProvider::disableEventDiscovery();
    }

    public function boot(): void
    {
        // Bill the tenant (Business), not the individual user.
        Cashier::useCustomerModel(Business::class);

        // Guard against silently dropping un-fillable attributes in dev. (Full
        // strict mode incl. lazy-loading prevention is intentionally avoided —
        // shared layout relations like the notification bell are loaded lazily.)
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        $this->bindTenantScopedModels();
        $this->configureRateLimiting();
    }

    /**
     * Scope route-model binding for {lead} to the authenticated user's
     * business. This runs after the `auth` middleware, so a lead belonging to
     * another tenant is reported as 404 (not 403) — it simply doesn't exist
     * for this user.
     */
    protected function bindTenantScopedModels(): void
    {
        Route::bind('lead', function (string $value) {
            $businessId = request()->user()?->business_id;

            return \App\Models\Lead::withoutGlobalScope(\App\Tenancy\TenantScope::class)
                ->where('business_id', $businessId)
                ->findOrFail($value);
        });
    }

    protected function configureRateLimiting(): void
    {
        // Inbound Twilio/Stripe webhooks – generous but bounded per IP.
        RateLimiter::for('webhooks', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));

        // JSON API.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by(
            optional($request->user())->id ?: $request->ip()
        ));
    }
}
