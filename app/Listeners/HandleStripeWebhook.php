<?php

namespace App\Listeners;

use App\Mail\PaymentFailedEmail;
use App\Mail\SubscriptionCancelledEmail;
use App\Mail\TrialEndingEmail;
use App\Models\Business;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * Reacts to raw Stripe webhooks (Cashier already keeps subscription state in
 * sync) to send the lifecycle emails our customers expect.
 */
class HandleStripeWebhook
{
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? null;
        $customerId = data_get($payload, 'data.object.customer');

        if (! $customerId) {
            return;
        }

        /** @var Business|null $business */
        $business = Cashier::findBillable($customerId);

        if (! $business) {
            return;
        }

        $recipient = $business->owner()?->email ?? $business->email;

        if (! $recipient) {
            return;
        }

        match ($type) {
            'invoice.payment_failed' => Mail::to($recipient)->send(new PaymentFailedEmail($business)),
            'customer.subscription.deleted' => Mail::to($recipient)->send(new SubscriptionCancelledEmail($business)),
            'customer.subscription.trial_will_end' => Mail::to($recipient)->send(new TrialEndingEmail($business)),
            default => null,
        };
    }
}
