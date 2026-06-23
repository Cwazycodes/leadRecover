<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Call;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Twilio\TwiML\VoiceResponse;

/**
 * Handles inbound Twilio Voice webhooks.
 *
 *  - `incoming`     : returned the moment a call hits the business number. We
 *                     try to forward it; if there's no forwarding number we
 *                     treat it as missed straight away.
 *  - `statusCallback`: the Dial "action" callback. A no-answer/busy/failed
 *                     result here is exactly a missed call → create the lead.
 */
class TwilioVoiceController extends Controller
{
    public function __construct(protected LeadService $leads) {}

    public function incoming(Request $request): Response
    {
        $business = $this->resolveBusiness($request->input('To'));
        $twiml = new VoiceResponse;

        if (! $business) {
            $twiml->hangup();

            return $this->twiml($twiml);
        }

        if ($business->forward_to_number) {
            $dial = $twiml->dial('', [
                'timeout' => 20,
                'action' => route('webhooks.twilio.voice.status'),
                'method' => 'POST',
            ]);
            $dial->number($business->forward_to_number);
        } else {
            // No-one to forward to → it's a missed call right now.
            $this->captureMissedCall($business, $request, 'no-answer');
            $twiml->say(
                'Sorry, we can\'t take your call right now. We\'ll text you straight away to help you book.',
                ['voice' => 'alice']
            );
            $twiml->hangup();
        }

        return $this->twiml($twiml);
    }

    public function statusCallback(Request $request): Response
    {
        $dialStatus = $request->input('DialCallStatus', $request->input('CallStatus'));

        if (in_array($dialStatus, Call::MISSED_STATUSES, true)) {
            if ($business = $this->resolveBusiness($request->input('To'))) {
                $this->captureMissedCall($business, $request, $dialStatus);
            }
        }

        return $this->twiml(new VoiceResponse);
    }

    protected function captureMissedCall(Business $business, Request $request, string $status): void
    {
        $this->leads->createFromMissedCall($business, [
            'call_sid' => $request->input('CallSid'),
            'from' => $request->input('From'),
            'to' => $request->input('To'),
            'status' => $status,
            'occurred_at' => now(),
        ]);
    }

    protected function resolveBusiness(?string $toNumber): ?Business
    {
        if (! $toNumber) {
            return null;
        }

        return Business::where('twilio_number', $toNumber)->first();
    }

    protected function twiml(VoiceResponse $response): Response
    {
        return response((string) $response, 200, ['Content-Type' => 'text/xml']);
    }
}
