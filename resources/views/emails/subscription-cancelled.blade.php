<x-mail::message>
# Your subscription has been cancelled

Hi {{ $business->name }},

We're sorry to see you go. Your LeadRecover subscription has been cancelled@if($endsAt) and your access will remain active until **{{ $endsAt->format('jS F Y') }}**@endif.

After that, we'll stop sending automated recovery messages for your missed calls. Your lead history will be kept safe in case you return.

Changed your mind? You can reactivate any time:

<x-mail::button :url="route('billing.index')">
Reactivate my plan
</x-mail::button>

Thanks for giving LeadRecover a try,<br>
The LeadRecover Team
</x-mail::message>
