<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Interaction;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Twilio\TwiML\MessagingResponse;

/**
 * Handles inbound SMS / WhatsApp messages and delivery-status callbacks.
 */
class TwilioMessageController extends Controller
{
    public function __construct(protected LeadService $leads) {}

    public function incoming(Request $request): Response
    {
        $from = $this->stripScheme($request->input('From', ''));
        $to = $this->stripScheme($request->input('To', ''));
        $channel = Str::startsWith((string) $request->input('From'), 'whatsapp:')
            ? Interaction::CHANNEL_WHATSAPP
            : Interaction::CHANNEL_SMS;
        $body = (string) $request->input('Body');

        $business = Business::where('twilio_number', $to)->first();

        if ($business) {
            $lead = $business->leads()
                ->where('phone', $from)
                ->open()
                ->latest()
                ->first()
                ?? $business->leads()->create([
                    'phone' => $from,
                    'source' => $channel,
                    'status' => Lead::STATUS_NEW,
                    'last_interaction_at' => now(),
                ]);

            $this->leads->recordInbound($lead, $channel, $body, $request->input('MessageSid'));
        }

        // Empty TwiML — we reply through our own outbound flow, not auto-reply.
        return response((string) new MessagingResponse, 200, ['Content-Type' => 'text/xml']);
    }

    public function statusCallback(Request $request): Response
    {
        $sid = $request->input('MessageSid');
        $status = $request->input('MessageStatus');

        if ($sid && $status) {
            // No tenant is active here, so the scope is a no-op and we update
            // the matching interaction wherever it lives.
            Interaction::where('provider_sid', $sid)->update(['status' => $status]);
        }

        return response('', 204);
    }

    protected function stripScheme(string $number): string
    {
        return Str::after($number, 'whatsapp:') ?: $number;
    }
}
