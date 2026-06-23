<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbound_sms_creates_a_lead_and_marks_it_responded(): void
    {
        $business = Business::factory()->create(['twilio_number' => '+447700900000']);
        User::factory()->for($business)->create(['role' => 'owner']);

        $response = $this->post(route('webhooks.twilio.message'), [
            'From' => '+447700900999',
            'To' => '+447700900000',
            'Body' => 'Hi, can I book for Friday?',
            'MessageSid' => 'SM-test-1',
        ]);

        $response->assertOk();

        $lead = Lead::where('phone', '+447700900999')->first();
        $this->assertNotNull($lead);
        $this->assertNotNull($lead->responded_at);
        $this->assertSame('contacted', $lead->status);

        $this->assertDatabaseHas('interactions', [
            'lead_id' => $lead->id,
            'direction' => 'inbound',
            'body' => 'Hi, can I book for Friday?',
        ]);
    }

    public function test_inbound_message_halts_follow_ups_for_an_existing_lead(): void
    {
        $business = Business::factory()->create(['twilio_number' => '+447700900000']);
        User::factory()->for($business)->create(['role' => 'owner']);
        $lead = Lead::factory()->for($business)->create([
            'phone' => '+447700900999',
            'status' => 'new',
            'follow_up_stage' => 0,
        ]);

        $this->post(route('webhooks.twilio.message'), [
            'From' => '+447700900999',
            'To' => '+447700900000',
            'Body' => 'Yes please',
            'MessageSid' => 'SM-test-2',
        ])->assertOk();

        $lead->refresh();
        $this->assertNotNull($lead->responded_at);
        // HaltFollowUps bumps the stage so pending reminder jobs no-op.
        $this->assertSame(2, $lead->follow_up_stage);
    }
}
