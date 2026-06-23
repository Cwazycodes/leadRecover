<?php

namespace App\Listeners;

use App\Events\LeadResponded;
use App\Notifications\LeadRepliedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * When a lead replies we advance their follow-up stage so any pending
 * reminder jobs short-circuit, and notify the team that someone responded.
 */
class HaltFollowUps
{
    public function handle(LeadResponded $event): void
    {
        $lead = $event->lead;

        if ($lead->follow_up_stage < 2) {
            $lead->forceFill(['follow_up_stage' => 2])->save();
        }

        if ($business = $lead->business) {
            Notification::send($business->users, new LeadRepliedNotification($lead, $event->interaction));
        }
    }
}
