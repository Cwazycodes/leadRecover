<?php

namespace Tests\Unit;

use App\Events\LeadCaptured;
use App\Events\LeadResponded;
use App\Models\Business;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LeadServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function service(): LeadService
    {
        return app(LeadService::class);
    }

    public function test_missed_call_creates_a_lead_call_and_event(): void
    {
        Event::fake([LeadCaptured::class]);
        $business = Business::factory()->create(['twilio_number' => '+447700900000']);

        $lead = $this->service()->createFromMissedCall($business, [
            'call_sid' => 'CA123',
            'from' => '+447700900999',
            'to' => '+447700900000',
            'status' => 'no-answer',
        ]);

        $this->assertSame('+447700900999', $lead->phone);
        $this->assertSame(Lead::STATUS_NEW, $lead->status);
        $this->assertDatabaseHas('calls', ['call_sid' => 'CA123', 'business_id' => $business->id]);
        Event::assertDispatched(LeadCaptured::class);
    }

    public function test_repeat_missed_call_reuses_the_open_lead(): void
    {
        Event::fake();
        $business = Business::factory()->create();
        $payload = ['from' => '+447700900999', 'to' => '+447700900000', 'status' => 'no-answer'];

        $this->service()->createFromMissedCall($business, $payload);
        $this->service()->createFromMissedCall($business, $payload);

        $this->assertSame(1, $business->leads()->count());
        $this->assertSame(2, $business->calls()->count());
    }

    public function test_record_inbound_marks_lead_responded_and_dispatches_event(): void
    {
        Event::fake([LeadResponded::class]);
        $business = Business::factory()->create();
        $lead = Lead::factory()->for($business)->create(['status' => Lead::STATUS_NEW]);

        $this->service()->recordInbound($lead, 'sms', 'Yes please!', 'SM1');

        $lead->refresh();
        $this->assertNotNull($lead->responded_at);
        $this->assertSame(Lead::STATUS_CONTACTED, $lead->status);
        $this->assertDatabaseHas('interactions', ['lead_id' => $lead->id, 'direction' => 'inbound', 'body' => 'Yes please!']);
        Event::assertDispatched(LeadResponded::class);
    }

    public function test_update_status_writes_a_system_timeline_entry(): void
    {
        Event::fake();
        $business = Business::factory()->create();
        $lead = Lead::factory()->for($business)->create(['status' => Lead::STATUS_NEW]);

        $this->service()->updateStatus($lead, Lead::STATUS_BOOKED);

        $this->assertSame(Lead::STATUS_BOOKED, $lead->fresh()->status);
        $this->assertDatabaseHas('interactions', [
            'lead_id' => $lead->id,
            'channel' => 'system',
            'body' => 'Status changed to booked.',
        ]);
    }
}
