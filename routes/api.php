<?php

use App\Services\AnalyticsService;
use App\Tenancy\Tenancy;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| The primary machine-to-machine surface for LeadRecover is the webhook set
| in routes/webhooks.php (Twilio) plus Cashier's Stripe webhook. This file
| exposes a small JSON surface for health checks and session-authenticated
| dashboard widgets.
*/

Route::middleware('throttle:api')->prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toIso8601String(),
    ]))->name('api.health');

    // Authenticated JSON: current business pipeline counts (cookie session).
    Route::middleware(['auth:web', 'tenant'])->get('/pipeline', function () {
        $business = app(Tenancy::class)->current();

        return response()->json(
            app(AnalyticsService::class)->statusBreakdown($business)
        );
    })->name('api.pipeline');
});
