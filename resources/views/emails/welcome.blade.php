<x-mail::message>
# Welcome to LeadRecover, {{ $user->name }}! 🎉

Your account for **{{ $business->name }}** is ready. From now on, every missed call can be turned into a booking automatically.

Here's how to get going in under 5 minutes:

1. **Connect your phone number** so we can catch missed calls.
2. **Set your booking link** (Calendly, your own URL, or our built-in page).
3. **Customise your SMS / WhatsApp message** — or use our proven defaults.

@if($trialEndsAt)
You're on a **free trial until {{ $trialEndsAt->format('jS F Y') }}** — no charge until then.
@endif

<x-mail::button :url="route('dashboard')">
Go to your dashboard
</x-mail::button>

Turn missed calls into paying customers,<br>
The LeadRecover Team
</x-mail::message>
