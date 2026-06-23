<?php

use App\Http\Middleware\EnsurePlatformAdmin;
use App\Http\Middleware\EnsureSubscribed;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\VerifyTwilioSignature;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Stateless inbound webhooks (Twilio). Registered outside the web
            // group so they carry no CSRF/session middleware.
            Route::middleware('throttle:webhooks')
                ->prefix('webhooks')
                ->as('webhooks.')
                ->group(base_path('routes/webhooks.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => IdentifyTenant::class,
            'subscribed' => EnsureSubscribed::class,
            'platform-admin' => EnsurePlatformAdmin::class,
            'twilio' => VerifyTwilioSignature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
