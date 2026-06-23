<x-layouts.dashboard title="Billing" header="Billing & plan">
    @php
        $hasSub = $subscription && $subscription->valid();
        $onGrace = $subscription && $subscription->onGracePeriod();
        $usagePct = $leadLimit ? min(100, round($leadsUsed / max($leadLimit, 1) * 100)) : 0;
    @endphp

    @if(request('checkout') === 'success')
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">🎉 You're subscribed! Thanks for choosing LeadRecover.</div>
    @elseif(request('checkout') === 'cancelled')
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Checkout cancelled — no charge was made.</div>
    @endif

    {{-- Status --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Current plan</p>
                    <p class="text-2xl font-bold text-slate-900">
                        {{ $currentPlanKey ? config("leadrecover.plans.$currentPlanKey.name") : ($onTrial ? 'Free trial' : 'No active plan') }}
                    </p>
                </div>
                @if($onGrace)
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Cancelling {{ $subscription->ends_at?->format('j M') }}</span>
                @elseif($hasSub)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">Active</span>
                @elseif($onTrial)
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700">Trial ends {{ $trialEndsAt?->format('j M') }}</span>
                @endif
            </div>

            @if($leadLimit)
                <div class="mt-5">
                    <div class="mb-1 flex justify-between text-xs text-slate-500">
                        <span>Recovered leads this month</span>
                        <span>{{ $leadsUsed }} / {{ number_format($leadLimit) }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ $usagePct > 90 ? 'bg-rose-500' : 'bg-brand-500' }}" style="width: {{ $usagePct }}%"></div>
                    </div>
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500">Unlimited recovered leads on this plan.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Payment method</p>
            @if($business->pm_last_four)
                <p class="mt-2 font-semibold text-slate-900">{{ ucfirst($business->pm_type) }} •••• {{ $business->pm_last_four }}</p>
            @else
                <p class="mt-2 text-sm text-slate-400">No card on file</p>
            @endif
            @if($business->hasStripeId())
                <a href="{{ route('billing.portal') }}" class="mt-4 inline-block rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Manage in Stripe →</a>
            @endif
        </div>
    </div>

    {{-- Plans --}}
    <div class="grid gap-6 lg:grid-cols-3">
        @foreach($plans as $key => $plan)
            @php $isCurrent = $currentPlanKey === $key; @endphp
            <div class="flex flex-col rounded-2xl border bg-white p-6 shadow-sm {{ $isCurrent ? 'border-brand-400 ring-2 ring-brand-500' : 'border-slate-200' }}">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">{{ $plan['name'] }}</h3>
                    @if($plan['most_popular'] ?? false)<span class="rounded-full bg-brand-100 px-2 py-0.5 text-xs font-medium text-brand-700">Popular</span>@endif
                </div>
                <p class="mt-3"><span class="text-3xl font-extrabold text-slate-900">£{{ $plan['price'] }}</span><span class="text-sm text-slate-500">/mo</span></p>
                <ul class="mt-4 flex-1 space-y-2 text-sm text-slate-600">
                    @foreach($plan['features'] as $feature)
                        <li class="flex gap-2"><svg class="mt-0.5 h-4 w-4 flex-none text-accent-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>{{ $feature }}</li>
                    @endforeach
                </ul>

                <div class="mt-6">
                    @if($isCurrent)
                        <button disabled class="w-full cursor-default rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-500">Current plan</button>
                    @elseif($hasSub)
                        <form method="POST" action="{{ route('billing.swap') }}">
                            @csrf <input type="hidden" name="plan" value="{{ $key }}">
                            <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                                Switch to {{ $plan['name'] }}
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('billing.checkout') }}">
                            @csrf <input type="hidden" name="plan" value="{{ $key }}">
                            <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                                Choose {{ $plan['name'] }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Cancel / resume --}}
    @if($hasSub && ! $onGrace)
        <div class="mt-6 flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <p class="font-medium text-slate-900">Cancel subscription</p>
                <p class="text-sm text-slate-500">You'll keep access until the end of your billing period.</p>
            </div>
            <form method="POST" action="{{ route('billing.cancel') }}" onsubmit="return confirm('Cancel your subscription?')">
                @csrf
                <button class="rounded-lg border border-rose-200 px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">Cancel plan</button>
            </form>
        </div>
    @elseif($onGrace)
        <div class="mt-6 flex items-center justify-between rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <div>
                <p class="font-medium text-amber-900">Your plan is scheduled to cancel</p>
                <p class="text-sm text-amber-700">Resume any time before {{ $subscription->ends_at?->format('j M Y') }} to keep things running.</p>
            </div>
            <form method="POST" action="{{ route('billing.resume') }}">
                @csrf
                <button class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Resume plan</button>
            </form>
        </div>
    @endif
</x-layouts.dashboard>
