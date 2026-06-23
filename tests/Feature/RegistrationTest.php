<?php

namespace Tests\Feature;

use App\Mail\WelcomeEmail;
use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_provisions_a_business_owner_and_trial(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'business_name' => 'Sharp Cuts Barbers',
            'industry' => 'barber',
            'name' => 'Jordan Blade',
            'email' => 'jordan@sharpcuts.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $business = Business::where('name', 'Sharp Cuts Barbers')->first();
        $this->assertNotNull($business);
        $this->assertSame('barber', $business->industry);
        $this->assertNotNull($business->trial_ends_at);

        $user = User::where('email', 'jordan@sharpcuts.test')->first();
        $this->assertSame($business->id, $user->business_id);
        $this->assertSame('owner', $user->role);

        Mail::assertQueued(WelcomeEmail::class);
    }

    public function test_registration_requires_a_business_name(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jordan',
            'email' => 'jordan@sharpcuts.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('business_name');
        $this->assertGuest();
    }
}
