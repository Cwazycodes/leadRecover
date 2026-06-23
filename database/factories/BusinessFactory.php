<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    public function definition(): array
    {
        $industries = array_keys(config('leadrecover.industries'));
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'email' => fake()->companyEmail(),
            'phone' => fake()->e164PhoneNumber(),
            'twilio_number' => fake()->e164PhoneNumber(),
            'forward_to_number' => fake()->e164PhoneNumber(),
            'industry' => fake()->randomElement($industries),
            'timezone' => 'Europe/London',
            'booking_type' => 'internal',
            'sms_template' => config('leadrecover.templates.sms'),
            'whatsapp_template' => config('leadrecover.templates.whatsapp'),
            'whatsapp_enabled' => fake()->boolean(30),
            'auto_respond_enabled' => true,
            'opening_hours' => [
                'monday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'tuesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'wednesday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'thursday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'friday' => ['open' => '09:00', 'close' => '17:00', 'closed' => false],
                'saturday' => ['open' => '10:00', 'close' => '14:00', 'closed' => false],
                'sunday' => ['open' => '00:00', 'close' => '00:00', 'closed' => true],
            ],
            'trial_ends_at' => now()->addDays(config('leadrecover.trial_days')),
            'onboarded_at' => now(),
        ];
    }

    /**
     * Attach an active Stripe subscription for the given plan key.
     */
    public function subscribedTo(string $planKey): static
    {
        return $this->afterCreating(function (Business $business) use ($planKey): void {
            $priceId = config("leadrecover.plans.$planKey.stripe_price_id") ?? "price_demo_$planKey";

            $business->forceFill([
                'stripe_id' => 'cus_demo_'.Str::lower(Str::random(10)),
                'pm_type' => 'visa',
                'pm_last_four' => (string) fake()->numberBetween(1000, 9999),
                'trial_ends_at' => now()->subDays(5),
            ])->save();

            $business->subscriptions()->create([
                'type' => 'default',
                'stripe_id' => 'sub_demo_'.Str::lower(Str::random(12)),
                'stripe_status' => 'active',
                'stripe_price' => $priceId,
                'quantity' => 1,
                'created_at' => $business->created_at,
                'updated_at' => $business->created_at,
            ]);
        });
    }
}
