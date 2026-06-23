<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Call;
use App\Models\Lead;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_computes_business_kpis_correctly(): void
    {
        $business = Business::factory()->create();

        Lead::factory()->for($business)->count(2)->create(['status' => Lead::STATUS_NEW, 'estimated_value' => null]);
        Lead::factory()->for($business)->count(3)->create(['status' => Lead::STATUS_CONTACTED, 'estimated_value' => null]);
        Lead::factory()->for($business)->count(3)->create(['status' => Lead::STATUS_BOOKED, 'estimated_value' => 50]);
        Lead::factory()->for($business)->count(2)->create(['status' => Lead::STATUS_CONVERTED, 'estimated_value' => 100]);

        Call::factory()->for($business)->count(7)->create(['status' => 'no-answer', 'occurred_at' => now()]);

        $metrics = (new AnalyticsService)->forBusiness($business, 30);

        $this->assertSame(10, $metrics['leads_total']);
        $this->assertSame(8, $metrics['leads_recovered']);   // contacted + booked + converted
        $this->assertSame(5, $metrics['bookings']);          // booked + converted
        $this->assertSame(50.0, $metrics['conversion_rate']); // 5 / 10
        $this->assertSame(350.0, $metrics['revenue_recovered']); // 3*50 + 2*100
        $this->assertSame(7, $metrics['calls_missed']);
    }

    public function test_conversion_rate_is_zero_without_leads(): void
    {
        $business = Business::factory()->create();

        $metrics = (new AnalyticsService)->forBusiness($business, 30);

        $this->assertSame(0.0, $metrics['conversion_rate']);
        $this->assertSame(0, $metrics['leads_total']);
    }

    public function test_platform_mrr_sums_active_subscriptions(): void
    {
        Business::factory()->subscribedTo('growth')->create(); // £99
        Business::factory()->subscribedTo('starter')->create(); // £49

        $platform = (new AnalyticsService)->forPlatform();

        $this->assertSame(148.0, $platform['mrr']);
        $this->assertSame(2, $platform['active_subscriptions']);
    }
}
