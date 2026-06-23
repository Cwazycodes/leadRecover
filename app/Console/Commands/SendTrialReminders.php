<?php

namespace App\Console\Commands;

use App\Mail\TrialEndingEmail;
use App\Models\Business;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Emails businesses whose free trial ends in ~3 days and who haven't yet
 * subscribed. The 24h window means each business is reminded exactly once.
 */
class SendTrialReminders extends Command
{
    protected $signature = 'subscriptions:trial-reminders';

    protected $description = 'Email customers whose trial is about to end';

    public function handle(): int
    {
        $businesses = Business::query()
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now()->addDays(2), now()->addDays(3)])
            ->whereDoesntHave('subscriptions', fn ($q) => $q->where('stripe_status', 'active'))
            ->get();

        foreach ($businesses as $business) {
            if ($recipient = $business->owner()?->email ?? $business->email) {
                Mail::to($recipient)->send(new TrialEndingEmail($business));
            }
        }

        $this->info("Sent {$businesses->count()} trial reminder(s).");

        return self::SUCCESS;
    }
}
