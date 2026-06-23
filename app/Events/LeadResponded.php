<?php

namespace App\Events;

use App\Models\Interaction;
use App\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired the first time a lead replies. Halts the automated follow-up chain.
 */
class LeadResponded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Lead $lead, public Interaction $interaction) {}
}
