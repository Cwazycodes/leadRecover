<?php

namespace Database\Factories;

use App\Models\Interaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Interaction>
 */
class InteractionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'channel' => Interaction::CHANNEL_SMS,
            'direction' => Interaction::DIRECTION_OUTBOUND,
            'body' => fake()->sentence(),
            'status' => 'delivered',
            'provider_sid' => 'SM'.Str::lower(Str::random(30)),
        ];
    }

    public function inbound(): static
    {
        return $this->state(fn () => [
            'direction' => Interaction::DIRECTION_INBOUND,
            'status' => 'received',
            'body' => fake()->randomElement([
                'Yes please, can I book for Thursday?',
                'What times do you have available?',
                'Thanks for getting back to me!',
                'How much is it?',
            ]),
        ]);
    }

    public function system(): static
    {
        return $this->state(fn () => [
            'channel' => Interaction::CHANNEL_SYSTEM,
            'direction' => Interaction::DIRECTION_SYSTEM,
            'status' => null,
            'provider_sid' => null,
            'body' => 'Missed call received.',
        ]);
    }
}
