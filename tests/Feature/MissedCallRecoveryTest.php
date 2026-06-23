<?php

namespace Tests\Feature;

use App\Jobs\MarkLeadStaleJob;
use App\Jobs\SendFollowUpReminderJob;
use App\Jobs\SendInitialOutreachJob;
use App\Models\Business;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use App\Services\LeadService;
use App\Services\MessageComposer;
use App\Services\TwilioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MissedCallRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_missed_call_webhook_captures_a_lead_and_queues_the_recovery_flow(): void
    {
        Queue::fake();
        Notification::fake();

        $business = Business::factory()->create([
            'twilio_number' => '+447700900000',
            'forward_to_number' => null,
            'auto_respond_enabled' => true,
            'whatsapp_enabled' => false,
        ]);
        $owner = User::factory()->for($business)->create(['role' => 'owner']);

        $response = $this->post(route('webhooks.twilio.voice.status'), [
            'DialCallStatus' => 'no-answer',
            'CallSid' => 'CA-test-1',
            'From' => '+447700900999',
            'To' => '+447700900000',
        ]);

        $response->assertOk();

        $lead = Lead::where('phone', '+447700900999')->first();
        $this->assertNotNull($lead);
        $this->assertSame($business->id, $lead->business_id);
        $this->assertDatabaseHas('calls', ['call_sid' => 'CA-test-1', 'lead_id' => $lead->id]);

        // The full recovery sequence is scheduled.
        Queue::assertPushed(SendInitialOutreachJob::class);
        Queue::assertPushed(SendFollowUpReminderJob::class, 2); // 1h + 24h reminders
        Queue::assertPushed(MarkLeadStaleJob::class);

        // And the team is notified.
        Notification::assertSentTo($owner, NewLeadNotification::class);
    }

    public function test_initial_outreach_job_sends_and_records_the_first_message(): void
    {
        $business = Business::factory()->create(['auto_respond_enabled' => true, 'whatsapp_enabled' => false]);
        $lead = Lead::factory()->for($business)->create(['status' => 'new']);

        (new SendInitialOutreachJob($lead))->handle(
            app(TwilioService::class),
            app(LeadService::class),
            app(MessageComposer::class),
        );

        $this->assertDatabaseHas('interactions', [
            'lead_id' => $lead->id,
            'direction' => 'outbound',
            'channel' => 'sms',
        ]);
        $this->assertSame('contacted', $lead->fresh()->status);
    }

    public function test_incoming_call_without_forwarding_returns_twiml(): void
    {
        Queue::fake();
        $business = Business::factory()->create(['twilio_number' => '+447700900000', 'forward_to_number' => null]);

        $response = $this->post(route('webhooks.twilio.voice'), [
            'CallSid' => 'CA-test-2',
            'From' => '+447700900999',
            'To' => '+447700900000',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('text/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<Response>', $response->getContent());
    }
}
