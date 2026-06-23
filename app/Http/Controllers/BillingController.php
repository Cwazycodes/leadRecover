<?php

namespace App\Http\Controllers;

use App\Tenancy\Tenancy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Cashier\Exceptions\IncompletePayment;

class BillingController extends Controller
{
    public function __construct(protected Tenancy $tenancy)
    {
    }

    public function index(): View
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        return view('billing.index', [
            'business' => $business,
            'plans' => config('leadrecover.plans'),
            'currentPlanKey' => $business->planKey(),
            'subscription' => $business->subscription('default'),
            'onTrial' => $business->onTrial(),
            'trialEndsAt' => $business->trial_ends_at,
            'leadsUsed' => $business->leadsUsedThisMonth(),
            'leadLimit' => $business->leadLimit(),
        ]);
    }

    /**
     * Start a Stripe Checkout session for a brand-new subscription.
     */
    public function checkout(Request $request): mixed
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        $priceId = $this->priceIdFor($request);

        // Already subscribed? Swap instead of double-charging.
        if ($business->subscribed('default')) {
            return redirect()->route('billing.index');
        }

        return $business->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('billing.index').'?checkout=success',
                'cancel_url' => route('billing.index').'?checkout=cancelled',
            ]);
    }

    /**
     * Upgrade / downgrade an existing subscription with proration.
     */
    public function swap(Request $request): RedirectResponse
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        $priceId = $this->priceIdFor($request);
        $subscription = $business->subscription('default');

        if (! $subscription) {
            return redirect()->route('billing.index');
        }

        try {
            $subscription->swapAndInvoice($priceId);
        } catch (IncompletePayment $exception) {
            return redirect()->route('cashier.payment', [
                $exception->payment->id,
                'redirect' => route('billing.index'),
            ]);
        }

        return back()->with('success', 'Your plan has been updated.');
    }

    public function cancel(): RedirectResponse
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        $business->subscription('default')?->cancel();

        return back()->with('success', 'Your subscription will cancel at the end of the billing period.');
    }

    public function resume(): RedirectResponse
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        $subscription = $business->subscription('default');

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();

            return back()->with('success', 'Welcome back — your subscription has been resumed.');
        }

        return back();
    }

    /**
     * Redirect to the Stripe-hosted customer portal (cards, invoices, etc.).
     */
    public function portal(): RedirectResponse
    {
        $business = $this->tenancy->current();
        $this->authorize('manage', $business);

        if (! $business->hasStripeId()) {
            return redirect()->route('billing.index');
        }

        return $business->redirectToBillingPortal(route('billing.index'));
    }

    /**
     * Resolve and validate the Stripe price ID from the submitted plan key.
     */
    protected function priceIdFor(Request $request): string
    {
        $request->validate([
            'plan' => ['required', Rule::in(array_keys(config('leadrecover.plans')))],
        ]);

        return config("leadrecover.plans.{$request->input('plan')}.stripe_price_id");
    }
}
