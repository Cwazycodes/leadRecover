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
    }

    public function boot(): void
    {
        // Bill the tenant (Business), not the individual user.
        Cashier::useCustomerModel(Business::class);

        // Catch lazy-loading / missing-attribute bugs outside production.
        Model::shouldBeStrict(! $this->app->isProduction());

        $this->configureRateLimiting();
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
