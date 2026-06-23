<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Fires ~72h after the missed call. If the lead never replied and is still
 * open, it's marked "stale" so the pipeline reflects reality.
 */
class MarkLeadStaleJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead)
    {
    }

    public function handle(LeadService $leads): void
    {
        $lead = $this->lead->fresh();

        if ($lead && $lead->shouldFollowUp()) {
            $leads->updateStatus($lead, Lead::STATUS_STALE);
        }
    }
}
