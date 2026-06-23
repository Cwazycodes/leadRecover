<x-layouts.marketing title="Pricing · LeadRecover">
    <section class="mx-auto max-w-7xl px-6 py-20">
        <div class="mx-auto max-w-2xl text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900">Simple, honest pricing</h1>
            <p class="mt-4 text-lg text-slate-600">Start with a {{ $trialDays }}-day free trial. No card required. Cancel anytime.</p>
        </div>

        <div class="mx-auto mt-16 grid max-w-5xl gap-8 lg:grid-cols-3">
            @foreach($plans as $key => $plan)
                <div class="relative flex flex-col rounded-3xl border bg-white p-8 shadow-sm
                            {{ ($plan['most_popular'] ?? false) ? 'border-brand-300 ring-2 ring-brand-500' : 'border-slate-200' }}">
                    @if($plan['most_popular'] ?? false)
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold text-white">Most popular</span>
                    @endif

                    <h2 class="text-lg font-semibold text-slate-900">{{ $plan['name'] }}</h2>
                    <div class="mt-4 flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold tracking-tight text-slate-900">£{{ $plan['price'] }}</span>
                        <span class="text-sm font-medium text-slate-500">/month</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $plan['lead_limit'] ? number_format($plan['lead_limit']).' recovered leads / month' : 'Unlimited recovered leads' }}
                    </p>

                    <ul class="mt-6 space-y-3 text-sm text-slate-600">
                        @foreach($plan['features'] as $feature)
                            <li class="flex gap-2">
                                <svg class="mt-0.5 h-4 w-4 flex-none text-accent-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8 pt-2">
                        @auth
                            <a href="{{ route('billing.index') }}" class="block rounded-lg px-4 py-2.5 text-center text-sm font-semibold transition
                                {{ ($plan['most_popular'] ?? false) ? 'bg-brand-600 text-white hover:bg-brand-700' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">
                                Choose {{ $plan['name'] }}
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="block rounded-lg px-4 py-2.5 text-center text-sm font-semibold transition
                                {{ ($plan['most_popular'] ?? false) ? 'bg-brand-600 text-white hover:bg-brand-700' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }}">
                                Start free trial
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mx-auto mt-12 max-w-xl text-center text-sm text-slate-500">
            All plans include automated SMS recovery, the lead dashboard and email support. Prices in GBP, billed monthly via Stripe.
        </p>
    </section>
</x-layouts.marketing>
