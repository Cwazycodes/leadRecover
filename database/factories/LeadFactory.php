<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        $hasName = fake()->boolean(70);

        return [
            'name' => $hasName ? fake()->name() : null,
            'phone' => fake()->e164PhoneNumber(),
            'email' => fake()->boolean(40) ? fake()->safeEmail() : null,
            'source' => fake()->randomElement(['missed_call', 'missed_call', 'missed_call', 'manual', 'whatsapp', 'web_form']),
            'status' => Lead::STATUS_NEW,
            'estimated_value' => fake()->boolean(40) ? fake()->randomFloat(2, 30, 600) : null,
            'last_interaction_at' => now(),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    /**
     * A lead that replied to our outreach.
     */
    public function responded(): static
    {
        return $this->state(fn () => [
            'responded_at' => now()->subHours(fake()->numberBetween(1, 48)),
            'follow_up_stage' => 2,
        ]);
    }
}
