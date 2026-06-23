<x-mail::message>
# Your trial ends soon ⏳

Hi {{ $business->name }},

Your LeadRecover free trial @if($trialEndsAt) ends on **{{ $trialEndsAt->format('jS F Y') }}** @else ends soon @endif.

To keep recovering missed-call revenue without interruption, choose a plan before then. It only takes a moment.

<x-mail::button :url="route('billing.index')">
Choose your plan
</x-mail::button>

Questions about which plan fits? Just reply to this email — we're happy to help.

The LeadRecover Team
</x-mail::message>
