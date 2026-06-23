<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_billing(): void
    {
        $this->get(route('billing.index'))->assertRedirect(route('login'));
    }

    public function test_owner_sees_the_billing_page_with_plans(): void
    {
        $owner = User::factory()->for(Business::factory())->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('Growth')
            ->assertSee('Pro');
    }

    public function test_checkout_rejects_an_unknown_plan(): void
    {
        $owner = User::factory()->for(Business::factory())->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->post(route('billing.checkout'), ['plan' => 'enterprise'])
            ->assertSessionHasErrors('plan');
    }

    public function test_staff_cannot_manage_billing(): void
    {
        $business = Business::factory()->create();
        $staff = User::factory()->for($business)->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('billing.index'))
            ->assertForbidden();
    }
}
