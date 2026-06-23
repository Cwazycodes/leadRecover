<?php

namespace App\Listeners;

use App\Events\LeadCaptured;
use App\Jobs\MarkLeadStaleJob;
use App\Jobs\SendFollowUpReminderJob;
use App\Jobs\SendInitialOutreachJob;

/**
 * Kicks off the automated recovery sequence for a newly captured lead:
 * immediate outreach, then timed reminders, then a stale sweep.
 */
class TriggerRecoveryFlow
{
    public function handle(LeadCaptured $event): void
    {
        $lead = $event->lead;
        $cadence = config('leadrecover.follow_ups');

        SendInitialOutreachJob::dispatch($lead);

        SendFollowUpReminderJob::dispatch($lead, 1)
            ->delay(now()->addMinutes($cadence['first_reminder_minutes']));

        SendFollowUpReminderJob::dispatch($lead, 2)
            ->delay(now()->addMinutes($cadence['second_reminder_minutes']));

        MarkLeadStaleJob::dispatch($lead)
            ->delay(now()->addMinutes($cadence['stale_after_minutes']));
    }
}
