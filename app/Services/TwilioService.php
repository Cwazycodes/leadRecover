<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Twilio\Rest\Client;
use Twilio\Security\RequestValidator;

/**
 * Thin wrapper around the Twilio REST API for SMS / WhatsApp plus inbound
 * webhook signature validation.
 *
 * In local/dev (TWILIO_ENABLED=false) the service logs the outgoing message
 * and returns a fake SID instead of hitting the API, so the whole recovery
 * flow can be exercised without live credentials.
 */
class TwilioService
{
    protected ?Client $client = null;

    public function __construct(
        protected ?string $sid = null,
        protected ?string $token = null,
        protected ?string $smsFrom = null,
        protected ?string $whatsappFrom = null,
        protected bool $enabled = false,
    ) {
        $this->sid ??= config('services.twilio.sid');
        $this->token ??= config('services.twilio.token');
        $this->smsFrom ??= config('services.twilio.from');
        $this->whatsappFrom ??= config('services.twilio.whatsapp_from');
        $this->enabled = (bool) config('services.twilio.enabled');
    }

    /**
     * Send an SMS message.
     *
     * @return array{sid: string, status: string}
     */
    public function sendSms(string $to, string $body, ?string $statusCallback = null): array
    {
        return $this->dispatch($this->smsFrom, $to, $body, $statusCallback, 'sms');
    }

    /**
     * Send a WhatsApp message. Numbers are normalised to the whatsapp: scheme.
     *
     * @return array{sid: string, status: string}
     */
    public function sendWhatsApp(string $to, string $body, ?string $statusCallback = null): array
    {
        $from = Str::startsWith((string) $this->whatsappFrom, 'whatsapp:')
            ? $this->whatsappFrom
            : 'whatsapp:'.$this->whatsappFrom;

        $to = Str::startsWith($to, 'whatsapp:') ? $to : 'whatsapp:'.$to;

        return $this->dispatch($from, $to, $body, $statusCallback, 'whatsapp');
    }

    /**
     * @return array{sid: string, status: string}
     */
    protected function dispatch(?string $from, string $to, string $body, ?string $statusCallback, string $channel): array
    {
        if (! $this->enabled) {
            Log::info('[Twilio:disabled] message not sent', compact('channel', 'to', 'body'));

            return ['sid' => 'SIMULATED-'.Str::upper(Str::random(24)), 'status' => 'queued'];
        }

        $options = ['from' => $from, 'body' => $body];

        if ($statusCallback) {
            $options['statusCallback'] = $statusCallback;
        }

        $message = $this->client()->messages->create($to, $options);

        return ['sid' => $message->sid, 'status' => $message->status ?? 'queued'];
    }

    /**
     * Validate an inbound Twilio webhook signature (X-Twilio-Signature).
     */
    public function validateSignature(Request $request): bool
    {
        if (! config('services.twilio.validate_signature')) {
            return true;
        }

        if (! $this->token) {
            return false;
        }

        $signature = $request->header('X-Twilio-Signature', '');
        $validator = new RequestValidator($this->token);

        return $validator->validate($signature, $request->fullUrl(), $request->post());
    }

    protected function client(): Client
    {
        return $this->client ??= new Client($this->sid, $this->token);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
