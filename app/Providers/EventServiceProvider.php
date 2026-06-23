<?php

namespace App\Providers;

use App\Events\LeadCaptured;
use App\Events\LeadResponded;
use App\Listeners\HaltFollowUps;
use App\Listeners\HandleStripeWebhook;
use App\Listeners\SendNewLeadNotification;
use App\Listeners\TriggerRecoveryFlow;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        LeadCaptured::class => [
            TriggerRecoveryFlow::class,
            SendNewLeadNotification::class,
        ],
        LeadResponded::class => [
            HaltFollowUps::class,
        ],
        WebhookReceived::class => [
            HandleStripeWebhook::class,
        ],
    ];
}
