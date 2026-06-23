<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function owner(): User
    {
        return User::factory()->for(Business::factory())->create(['role' => 'owner']);
    }

    public function test_guests_are_redirected_from_leads(): void
    {
        $this->get(route('leads.index'))->assertRedirect(route('login'));
    }

    public function test_owner_can_view_the_leads_index(): void
    {
        $this->actingAs($this->owner())
            ->get(route('leads.index'))
            ->assertOk()
            ->assertSee('Leads');
    }

    public function test_owner_can_create_a_lead(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->post(route('leads.store'), [
            'name' => 'Pat Customer',
            'phone' => '+447700900123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'business_id' => $owner->business_id,
            'phone' => '+447700900123',
            'source' => 'manual',
        ]);
    }

    public function test_owner_can_update_a_lead_status(): void
    {
        $owner = $this->owner();
        $lead = Lead::factory()->for($owner->business)->create(['status' => 'new']);

        $this->actingAs($owner)
            ->patch(route('leads.update', $lead), ['status' => 'booked'])
            ->assertRedirect(route('leads.show', $lead));

        $this->assertSame('booked', $lead->fresh()->status);
    }

    public function test_sending_a_note_is_recorded_on_the_timeline(): void
    {
        $owner = $this->owner();
        $lead = Lead::factory()->for($owner->business)->create();

        $this->actingAs($owner)->post(route('leads.messages.store', $lead), [
            'channel' => 'note',
            'body' => 'Called back, left voicemail.',
        ])->assertRedirect();

        $this->assertDatabaseHas('interactions', [
            'lead_id' => $lead->id,
            'channel' => 'note',
            'body' => 'Called back, left voicemail.',
        ]);
    }
}
