<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed with guarding disabled so we can backdate created_at columns.
        Model::unguard();

        $this->createPlatformAdmin();
        $this->createDemoBusiness();
        $this->createSampleBusinesses();

        Model::reguard();
    }

    protected function createPlatformAdmin(): void
    {
        User::factory()->platformAdmin()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@leadrecover.test',
        ]);
    }

    /**
     * A fully-populated demo account you can log into:
     *   demo@leadrecover.test / password
     */
    protected function createDemoBusiness(): void
    {
        $business = Business::factory()
            ->subscribedTo('growth')
            ->create([
                'name' => 'Bright Smile Dental',
                'slug' => 'bright-smile-dental',
                'email' => 'hello@brightsmile.test',
                'industry' => 'dentist',
                'twilio_number' => '+447700900000',
                'forward_to_number' => '+447700900111',
                'whatsapp_enabled' => true,
            ]);

        $business->users()->create([
            'name' => 'Dana Owner',
            'email' => 'demo@leadrecover.test',
            'password' => 'password',
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $business->users()->create([
            'name' => 'Sam Reception',
            'email' => 'staff@leadrecover.test',
            'password' => 'password',
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $this->seedLeads($business, 48, 60);
    }

    protected function createSampleBusinesses(): void
    {
        $plans = ['starter', 'growth', 'pro', 'starter', 'growth', 'pro'];

        foreach ($plans as $index => $plan) {
            $signedUp = now()->subMonths(5 - ($index % 6))->subDays(rand(0, 20));

            $factory = Business::factory();

            // Mix of paying and still-trialing accounts.
            if ($index % 4 !== 0) {
                $factory = $factory->subscribedTo($plan);
            }

            $business = $factory->create([
                'created_at' => $signedUp,
                'updated_at' => $signedUp,
            ]);

            $business->users()->create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => 'password',
                'role' => 'owner',
                'email_verified_at' => now(),
            ]);

            $this->seedLeads($business, rand(8, 25), 45);
        }
    }

    /**
     * Create a realistic spread of leads (with calls + conversation history)
     * for a business, backdated across the given window.
     */
    protected function seedLeads(Business $business, int $count, int $daysBack): void
    {
        $distribution = [
            Lead::STATUS_NEW => 22,
            Lead::STATUS_CONTACTED => 28,
            Lead::STATUS_BOOKED => 16,
            Lead::STATUS_CONVERTED => 14,
            Lead::STATUS_LOST => 12,
            Lead::STATUS_STALE => 8,
        ];

        $pool = [];
        foreach ($distribution as $status => $weight) {
            $pool = array_merge($pool, array_fill(0, $weight, $status));
        }

        for ($i = 0; $i < $count; $i++) {
            $status = $pool[array_rand($pool)];
            $createdAt = now()->subDays(rand(0, $daysBack))->subMinutes(rand(0, 1440));

            $engaged = in_array($status, [Lead::STATUS_CONTACTED, Lead::STATUS_BOOKED, Lead::STATUS_CONVERTED], true);
            $booked = in_array($status, [Lead::STATUS_BOOKED, Lead::STATUS_CONVERTED], true);

            $lead = $business->leads()->create([
                'name' => fake()->boolean(70) ? fake()->name() : null,
                'phone' => fake()->e164PhoneNumber(),
                'email' => fake()->boolean(35) ? fake()->safeEmail() : null,
                'source' => fake()->randomElement(['missed_call', 'missed_call', 'missed_call', 'whatsapp', 'manual']),
                'status' => $status,
                'estimated_value' => $booked ? fake()->randomFloat(2, 40, 600) : null,
                'booking_date' => $booked ? $createdAt->copy()->addDays(rand(1, 10)) : null,
                'responded_at' => $engaged && fake()->boolean(70) ? $createdAt->copy()->addMinutes(rand(5, 240)) : null,
                'follow_up_stage' => $engaged ? 1 : 0,
                'last_contacted_at' => $createdAt->copy()->addMinutes(2),
                'last_interaction_at' => $createdAt->copy()->addMinutes(rand(2, 600)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if ($lead->source === 'missed_call') {
                $business->calls()->create([
                    'lead_id' => $lead->id,
                    'call_sid' => 'CA'.fake()->unique()->bothify('##############################'),
                    'from_number' => $lead->phone,
                    'to_number' => $business->twilio_number ?? '+447700900000',
                    'direction' => 'inbound',
                    'status' => 'no-answer',
                    'occurred_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            $this->seedConversation($business, $lead, $createdAt, $engaged, $booked);
        }
    }

    protected function seedConversation(Business $business, Lead $lead, Carbon $createdAt, bool $engaged, bool $booked): void
    {
        $make = function (array $attributes) use ($business, $lead): void {
            $lead->interactions()->create(array_merge([
                'business_id' => $business->id,
            ], $attributes));
        };

        $make([
            'channel' => 'system', 'direction' => 'system', 'body' => 'Missed call received.',
            'created_at' => $createdAt, 'updated_at' => $createdAt,
        ]);

        $make([
            'channel' => $business->whatsapp_enabled ? 'whatsapp' : 'sms', 'direction' => 'outbound',
            'body' => 'Sorry we missed your call! Click here to book an appointment.',
            'status' => 'delivered', 'provider_sid' => 'SM'.fake()->bothify('##############'),
            'created_at' => $createdAt->copy()->addMinutes(1), 'updated_at' => $createdAt->copy()->addMinutes(1),
        ]);

        if ($engaged && $lead->responded_at) {
            $make([
                'channel' => $business->whatsapp_enabled ? 'whatsapp' : 'sms', 'direction' => 'inbound',
                'body' => fake()->randomElement(['Yes please, can I book?', 'What times are free?', 'How much is it?']),
                'status' => 'received', 'provider_sid' => 'SM'.fake()->bothify('##############'),
                'created_at' => $lead->responded_at, 'updated_at' => $lead->responded_at,
            ]);
        }

        if ($booked) {
            $make([
                'channel' => 'note', 'direction' => 'system',
                'body' => 'Appointment confirmed for '.$lead->booking_date?->format('jS M Y H:i'),
                'created_at' => $lead->last_interaction_at, 'updated_at' => $lead->last_interaction_at,
            ]);
        }
    }
}
