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
 * Automated reminder. Stage 1 fires ~1h after the missed call, stage 2 ~24h.
 * Self-guards: if the lead has already replied or been closed, it no-ops so a
 * paying/booked customer is never pestered.
 */
class SendFollowUpReminderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead, public int $stage) {}

    public function handle(TwilioService $twilio, LeadService $leads, MessageComposer $composer): void
    {
        $lead = $this->lead->fresh();

        if (! $lead || ! $lead->business || ! $lead->shouldFollowUp()) {
            return;
        }

        // Don't re-send a stage we've already passed.
        if ($lead->follow_up_stage >= $this->stage) {
            return;
        }

        $business = $lead->business;
        $channel = $business->whatsapp_enabled ? 'whatsapp' : 'sms';
        $templateStage = $this->stage === 1 ? 'first_reminder' : 'second_reminder';
        $body = $composer->forStage($templateStage, $business, $lead, $channel);

        try {
            $result = $channel === 'whatsapp'
                ? $twilio->sendWhatsApp($lead->phone, $body)
                : $twilio->sendSms($lead->phone, $body);

            $leads->recordOutbound($lead, $channel, $body, $result);
            $lead->forceFill(['follow_up_stage' => $this->stage])->save();
        } catch (\Throwable $e) {
            Log::error('Follow-up reminder failed', [
                'lead_id' => $lead->id, 'stage' => $this->stage, 'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
