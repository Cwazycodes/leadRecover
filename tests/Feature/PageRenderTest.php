<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function owner(): User
    {
        return User::factory()->for(Business::factory())->create(['role' => 'owner']);
    }

    public function test_dashboard_renders(): void
    {
        $owner = $this->owner();
        Lead::factory()->for($owner->business)->count(3)->create();

        $this->actingAs($owner)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Calls missed')
            ->assertSee('Revenue recovered');
    }

    public function test_settings_page_renders(): void
    {
        $this->actingAs($this->owner())->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Business profile')
            ->assertSee('Opening hours');
    }

    public function test_lead_detail_renders(): void
    {
        $owner = $this->owner();
        $lead = Lead::factory()->for($owner->business)->create();

        $this->actingAs($owner)->get(route('leads.show', $lead))
            ->assertOk()
            ->assertSee('Activity');
    }

    public function test_admin_dashboard_renders_for_platform_admin(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        Business::factory()->subscribedTo('growth')->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Platform overview')
            ->assertSee('MRR');
    }

    public function test_admin_customer_pages_render(): void
    {
        $admin = User::factory()->platformAdmin()->create();
        $business = Business::factory()->create();
        Lead::factory()->for($business)->count(2)->create();

        $this->actingAs($admin)->get(route('admin.customers.index'))->assertOk()->assertSee($business->name);
        $this->actingAs($admin)->get(route('admin.customers.show', $business))->assertOk()->assertSee('Pipeline');
    }

    public function test_non_admin_cannot_access_admin_area(): void
    {
        $this->actingAs($this->owner())->get(route('admin.dashboard'))->assertForbidden();
    }
}
