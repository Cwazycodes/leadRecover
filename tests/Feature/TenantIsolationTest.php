<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_cannot_view_another_businesss_lead(): void
    {
        $businessA = Business::factory()->create();
        $ownerA = User::factory()->for($businessA)->create(['role' => 'owner']);

        $businessB = Business::factory()->create();
        $leadB = Lead::factory()->for($businessB)->create();

        $this->actingAs($ownerA)
            ->get(route('leads.show', $leadB))
            ->assertNotFound();
    }

    public function test_lead_index_is_scoped_to_the_current_business(): void
    {
        $businessA = Business::factory()->create();
        $ownerA = User::factory()->for($businessA)->create(['role' => 'owner']);
        $leadA = Lead::factory()->for($businessA)->create(['phone' => '+440000000001']);

        $businessB = Business::factory()->create();
        $leadB = Lead::factory()->for($businessB)->create(['phone' => '+440000000002']);

        $response = $this->actingAs($ownerA)->get(route('leads.index'));

        $response->assertOk();
        $response->assertSee($leadA->phone);
        $response->assertDontSee($leadB->phone);
    }

    public function test_updating_another_businesss_lead_is_forbidden(): void
    {
        $ownerA = User::factory()->for(Business::factory())->create(['role' => 'owner']);
        $leadB = Lead::factory()->for(Business::factory())->create();

        // Route-model binding is tenant-scoped, so the foreign lead 404s.
        $this->actingAs($ownerA)
            ->patch(route('leads.update', $leadB), ['status' => 'booked'])
            ->assertNotFound();
    }
}
