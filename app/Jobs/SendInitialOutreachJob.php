<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Services\LeadService;
use App\Services\MessageComposer;
use App\Services\TwilioService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Sends the very first "sorry we missed your call" message via the business's
 * preferred channel (WhatsApp when enabled, otherwise SMS).
 */
class SendInitialOutreachJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function handle(TwilioService $twilio, LeadService $leads, MessageComposer $composer): void
    {
        $lead = $this->lead;
        $business = $lead->business;

        if (! $business || ! $business->auto_respond_enabled) {
            return;
        }

        $channel = $business->whatsapp_enabled ? 'whatsapp' : 'sms';
        $body = $composer->forStage('initial', $business, $lead, $channel);

        try {
            $result = $channel === 'whatsapp'
                ? $twilio->sendWhatsApp($lead->phone, $body)
                : $twilio->sendSms($lead->phone, $body);

            $leads->recordOutbound($lead, $channel, $body, $result);
        } catch (\Throwable $e) {
            Log::error('Initial outreach failed', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);

            throw $e;
        }
    }
}
