<x-layouts.marketing>
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 to-white"></div>
        <div class="mx-auto max-w-7xl px-6 pb-20 pt-16 sm:pt-24">
            <div class="mx-auto max-w-3xl text-center">
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white px-3 py-1 text-xs font-medium text-brand-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-500"></span>
                    For dentists, salons, clinics &amp; local pros
                </span>
                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl">
                    Turn missed calls into <span class="bg-gradient-to-r from-brand-600 to-accent-500 bg-clip-text text-transparent">paying customers</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-600">
                    Every missed call is lost revenue. LeadRecover instantly texts back the caller, captures their details and nudges them to book — automatically, 24/7.
                </p>
                <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="w-full rounded-lg bg-brand-600 px-6 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700 sm:w-auto">
                        Start your free trial
                    </a>
                    <a href="{{ route('pricing') }}" class="w-full rounded-lg border border-slate-300 bg-white px-6 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">
                        See pricing
                    </a>
                </div>
                <p class="mt-4 text-xs text-slate-500">No card required · {{ config('leadrecover.trial_days') }}-day free trial · Cancel anytime</p>
            </div>

            {{-- Mock conversation --}}
            <div class="mx-auto mt-16 max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-xl">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs font-medium text-slate-400">
                    <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                    Missed call · 2 mins ago
                </div>
                <div class="space-y-3 pt-4">
                    <div class="max-w-[80%] rounded-2xl rounded-tl-sm bg-slate-100 px-4 py-2 text-sm text-slate-700">
                        ☎️ Missed call from +44 7700 900321
                    </div>
                    <div class="ml-auto max-w-[85%] rounded-2xl rounded-tr-sm bg-brand-600 px-4 py-2 text-sm text-white">
                        Sorry we missed your call at Bright Smile Dental! Click here to book an appointment 👉 brightsmile.book
                    </div>
                    <div class="max-w-[80%] rounded-2xl rounded-tl-sm bg-slate-100 px-4 py-2 text-sm text-slate-700">
                        Oh great — yes I'd like Thursday afternoon please 🙌
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stat strip --}}
    <section class="border-y border-slate-100 bg-slate-50">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-6 py-10 text-center md:grid-cols-4">
            @foreach([['62%','of callers never call back'],['78%','choose the business that replies first'],['£1,200','avg. monthly revenue recovered'],['<60s','to an automatic reply']] as $stat)
                <div>
                    <p class="text-3xl font-extrabold text-slate-900">{{ $stat[0] }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- How it works --}}
    <section id="how" class="mx-auto max-w-7xl px-6 py-20">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Recover lost bookings in 3 steps</h2>
            <p class="mt-4 text-slate-600">Connect once and let LeadRecover work in the background.</p>
        </div>
        <div class="mt-14 grid gap-8 md:grid-cols-3">
            @foreach([
                ['1','Connect your number','Point your business phone to LeadRecover. Setup takes minutes, no new hardware.'],
                ['2','We catch missed calls','The moment a call goes unanswered, we create a lead and text the caller back instantly.'],
                ['3','They book, you win','Callers tap your booking link. New appointments land straight in your dashboard.'],
            ] as $step)
                <div class="relative rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-lg font-bold text-white">{{ $step[0] }}</span>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $step[1] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $step[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="bg-slate-50 py-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">Everything you need to stop losing customers</h2>
            </div>
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['Instant SMS &amp; WhatsApp', 'Auto-reply on the channel your customers actually read — within seconds of a missed call.'],
                    ['Smart follow-ups', 'No reply? We send a gentle nudge after 1 hour and 24 hours, then mark the lead so nothing slips.'],
                    ['Lead pipeline', 'Track every lead from new → contacted → booked → converted in a clean, simple dashboard.'],
                    ['Booking links', 'Use Calendly, your own URL, or our built-in booking page. Whatever works for you.'],
                    ['Revenue analytics', 'See calls missed, leads recovered and the revenue you\'ve won back — at a glance.'],
                    ['Built for teams', 'Multiple users, roles and per-business settings with strict data isolation.'],
                ] as $feature)
                    <div class="rounded-2xl border border-slate-200 bg-white p-6">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-accent-500/10 text-accent-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900">{!! $feature[0] !!}</h3>
                        <p class="mt-2 text-sm text-slate-600">{!! $feature[1] !!}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Industries --}}
    <section id="industries" class="mx-auto max-w-7xl px-6 py-20">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-slate-900">Loved by local businesses</h2>
            <p class="mt-4 text-slate-600">If you book appointments and answer the phone, LeadRecover pays for itself.</p>
        </div>
        <div class="mt-12 flex flex-wrap justify-center gap-3">
            @foreach($industries as $industry)
                @if($industry['label'] !== 'Other')
                    <span class="rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-700 shadow-sm">{{ $industry['label'] }}</span>
                @endif
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-7xl px-6 pb-24">
        <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 px-8 py-16 text-center shadow-xl">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Stop letting bookings ring out</h2>
            <p class="mx-auto mt-4 max-w-xl text-brand-100">Join local businesses recovering thousands in missed-call revenue every month.</p>
            <a href="{{ route('register') }}" class="mt-8 inline-block rounded-lg bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50">
                Start your free {{ config('leadrecover.trial_days') }}-day trial
            </a>
        </div>
    </section>
</x-layouts.marketing>
