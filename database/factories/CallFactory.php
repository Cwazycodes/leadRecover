<?php

namespace Database\Factories;

use App\Models\Call;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Call>
 */
class CallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'call_sid' => 'CA'.Str::lower(Str::random(30)),
            'from_number' => fake()->e164PhoneNumber(),
            'to_number' => fake()->e164PhoneNumber(),
            'direction' => 'inbound',
            'status' => fake()->randomElement(['no-answer', 'busy', 'missed']),
            'duration' => 0,
            'occurred_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'duration' => fake()->numberBetween(30, 600),
        ]);
    }
}
