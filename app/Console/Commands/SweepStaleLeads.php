<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;

/**
 * Safety net for the queued MarkLeadStaleJob: marks any still-open,
 * unresponsive lead older than the configured stale window. Runs across all
 * tenants (no tenant is active in the console, so the global scope is a no-op).
 */
class SweepStaleLeads extends Command
{
    protected $signature = 'leads:sweep-stale';

    protected $description = 'Mark old, unresponsive leads as stale';

    public function handle(): int
    {
        $cutoff = now()->subMinutes(config('leadrecover.follow_ups.stale_after_minutes'));

        $count = Lead::query()
            ->whereNull('responded_at')
            ->whereIn('status', [Lead::STATUS_NEW, Lead::STATUS_CONTACTED])
            ->where('created_at', '<=', $cutoff)
            ->update(['status' => Lead::STATUS_STALE]);

        $this->info("Marked {$count} lead(s) as stale.");

        return self::SUCCESS;
    }
}
