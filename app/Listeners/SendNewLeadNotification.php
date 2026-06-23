<?php

namespace App\Listeners;

use App\Events\LeadCaptured;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Alerts the business's team (email + in-app) that a new lead just came in.
 */
class SendNewLeadNotification
{
    public function handle(LeadCaptured $event): void
    {
        $business = $event->lead->business;

        if (! $business) {
            return;
        }

        Notification::send($business->users, new NewLeadNotification($event->lead));
    }
}
