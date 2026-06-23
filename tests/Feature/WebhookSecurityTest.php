<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_twilio_webhook_is_rejected_without_a_valid_signature(): void
    {
        config()->set('services.twilio.validate_signature', true);
        config()->set('services.twilio.token', 'super-secret-token');

        Business::factory()->create(['twilio_number' => '+447700900000']);

        $this->post(route('webhooks.twilio.voice.status'), [
            'DialCallStatus' => 'no-answer',
            'From' => '+447700900999',
            'To' => '+447700900000',
        ])->assertForbidden();
    }

    public function test_twilio_webhook_is_accepted_when_signature_validation_is_disabled(): void
    {
        config()->set('services.twilio.validate_signature', false);

        Business::factory()->create(['twilio_number' => '+447700900000']);

        $this->post(route('webhooks.twilio.voice.status'), [
            'DialCallStatus' => 'no-answer',
            'From' => '+447700900999',
            'To' => '+447700900000',
        ])->assertOk();
    }
}
