<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired the moment a brand-new lead is created (most often from a missed
 * call). Drives the automated outreach + notifications.
 */
class LeadCaptured
{
    use Dispatchable, SerializesModels;

    public function __construct(public Lead $lead) {}
}
