<?php

namespace Tests\Feature;

use App\Jobs\MarkLeadStaleJob;
use App\Jobs\SendFollowUpReminderJob;
use App\Models\Business;
use App\Models\Lead;
use App\Services\LeadService;
use App\Services\MessageComposer;
use App\Services\TwilioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_is_sent_when_the_lead_has_not_responded(): void
    {
        $business = Business::factory()->create(['auto_respond_enabled' => true, 'whatsapp_enabled' => false]);
        $lead = Lead::factory()->for($business)->create([
            'status' => 'new',
            'responded_at' => null,
            'follow_up_stage' => 0,
        ]);

        (new SendFollowUpReminderJob($lead, 1))->handle(
            app(TwilioService::class),
            app(LeadService::class),
            app(MessageComposer::class),
        );

        $this->assertDatabaseHas('interactions', ['lead_id' => $lead->id, 'direction' => 'outbound']);
        $this->assertSame(1, $lead->fresh()->follow_up_stage);
    }

    public function test_reminder_is_skipped_when_the_lead_has_responded(): void
    {
        $business = Business::factory()->create();
        $lead = Lead::factory()->for($business)->create([
            'status' => 'contacted',
            'responded_at' => now(),
            'follow_up_stage' => 1,
        ]);

        (new SendFollowUpReminderJob($lead, 2))->handle(
            app(TwilioService::class),
            app(LeadService::class),
            app(MessageComposer::class),
        );

        $this->assertDatabaseMissing('interactions', ['lead_id' => $lead->id, 'direction' => 'outbound']);
    }

    public function test_stale_job_marks_unresponsive_leads_stale(): void
    {
        $business = Business::factory()->create();
        $lead = Lead::factory()->for($business)->create(['status' => 'contacted', 'responded_at' => null]);

        (new MarkLeadStaleJob($lead))->handle(app(LeadService::class));

        $this->assertSame('stale', $lead->fresh()->status);
    }

    public function test_stale_job_leaves_responded_leads_alone(): void
    {
        $business = Business::factory()->create();
        $lead = Lead::factory()->for($business)->create(['status' => 'booked', 'responded_at' => now()]);

        (new MarkLeadStaleJob($lead))->handle(app(LeadService::class));

        $this->assertSame('booked', $lead->fresh()->status);
    }
}
