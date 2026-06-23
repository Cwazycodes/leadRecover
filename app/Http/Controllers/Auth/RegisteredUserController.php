<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'industries' => config('leadrecover.industries'),
        ]);
    }

    /**
     * Handle an incoming registration request: provision a tenant business,
     * its owner user and a free trial in one transaction.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', Rule::in(array_keys(config('leadrecover.industries')))],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request): User {
            $business = Business::create([
                'name' => $request->business_name,
                'email' => $request->email,
                'industry' => $request->industry ?? 'other',
                'sms_template' => config('leadrecover.templates.sms'),
                'whatsapp_template' => config('leadrecover.templates.whatsapp'),
                'opening_hours' => $this->defaultOpeningHours(),
                'trial_ends_at' => now()->addDays(config('leadrecover.trial_days')),
                'onboarded_at' => now(),
            ]);

            return $business->users()->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'owner',
            ]);
        });

        event(new Registered($user));

        Mail::to($user->email)->send(new WelcomeEmail($user->business, $user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * @return array<string, array{open: string, close: string, closed: bool}>
     */
    protected function defaultOpeningHours(): array
    {
        $weekday = ['open' => '09:00', 'close' => '17:00', 'closed' => false];

        return [
            'monday' => $weekday,
            'tuesday' => $weekday,
            'wednesday' => $weekday,
            'thursday' => $weekday,
            'friday' => $weekday,
            'saturday' => ['open' => '10:00', 'close' => '14:00', 'closed' => false],
            'sunday' => ['open' => '00:00', 'close' => '00:00', 'closed' => true],
        ];
    }
}
