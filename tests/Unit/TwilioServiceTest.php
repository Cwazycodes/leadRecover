<?php

namespace Tests\Unit;

use App\Services\TwilioService;
use Illuminate\Http\Request;
use Tests\TestCase;

class TwilioServiceTest extends TestCase
{
    public function test_disabled_service_returns_a_simulated_sid_without_calling_the_api(): void
    {
        config()->set('services.twilio.enabled', false);

        $result = (new TwilioService)->sendSms('+447700900000', 'Hello');

        $this->assertStringStartsWith('SIMULATED-', $result['sid']);
        $this->assertSame('queued', $result['status']);
    }

    public function test_whatsapp_helper_returns_a_result_when_disabled(): void
    {
        config()->set('services.twilio.enabled', false);

        $result = (new TwilioService)->sendWhatsApp('+447700900000', 'Hi');

        $this->assertArrayHasKey('sid', $result);
    }

    public function test_signature_validation_is_skipped_when_disabled_in_config(): void
    {
        config()->set('services.twilio.validate_signature', false);

        $request = Request::create('https://example.com/webhooks/twilio/voice', 'POST');

        $this->assertTrue((new TwilioService)->validateSignature($request));
    }

    public function test_signature_validation_fails_without_a_token(): void
    {
        config()->set('services.twilio.validate_signature', true);
        config()->set('services.twilio.token', null);

        $request = Request::create('https://example.com/webhooks/twilio/voice', 'POST');

        $this->assertFalse((new TwilioService)->validateSignature($request));
    }
}
