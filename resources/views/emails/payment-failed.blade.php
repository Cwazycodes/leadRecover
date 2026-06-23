<x-mail::message>
# We couldn't process your payment

Hi {{ $business->name }},

Your most recent LeadRecover payment didn't go through. This usually happens when a card has expired or has insufficient funds.

To avoid any interruption to your missed-call recovery, please update your payment details:

<x-mail::button :url="route('billing.index')" color="error">
Update payment method
</x-mail::button>

We'll automatically retry the charge, but updating your card now is the fastest way to stay covered.

The LeadRecover Team
</x-mail::message>
