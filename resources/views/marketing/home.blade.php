<x-layouts.marketing>
    {{-- ================= HERO ================= --}}
    <section id="overview" class="relative overflow-hidden bg-white">
        <div class="mx-auto max-w-5xl px-5 pt-16 text-center sm:pt-24">
            <p class="text-lg font-semibold text-[#0066cc] sm:text-xl">LeadRecover</p>
            <h1 class="mx-auto mt-2 max-w-3xl text-5xl font-semibold tracking-tight text-[#1d1d1f] sm:text-7xl">
                Never miss a<br class="hidden sm:block"> customer again.
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-xl font-normal text-[#1d1d1f] sm:text-2xl">
                Every missed call is money walking out the door. LeadRecover texts them back in
                seconds — and turns it into a booking.
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-x-7 gap-y-3 text-[17px] sm:text-xl">
                <a href="{{ route('register') }}" class="text-[#0066cc] hover:underline">Start free trial <span aria-hidden="true">&rsaquo;</span></a>
                <a href="#features" class="text-[#0066cc] hover:underline">See how it works <span aria-hidden="true">&rsaquo;</span></a>
            </div>
        </div>

        {{-- iMessage-style device --}}
        <div class="mx-auto mt-14 flex max-w-5xl justify-center px-5 pb-20">
            <div class="w-[320px] rounded-[3rem] bg-black p-3 shadow-2xl ring-1 ring-black/10">
                <div class="relative overflow-hidden rounded-[2.4rem] bg-[#f5f5f7]">
                    <div class="absolute left-1/2 top-0 z-10 h-6 w-32 -translate-x-1/2 rounded-b-2xl bg-black"></div>
                    <div class="bg-white/80 px-5 pb-3 pt-9 text-center backdrop-blur">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-b from-[#0a84ff] to-[#0066cc] text-base font-semibold text-white">BS</div>
                        <p class="mt-1 text-[13px] font-semibold text-[#1d1d1f]">Bright Smile Dental</p>
                    </div>
                    <div class="space-y-2.5 px-4 py-5 text-[14px]">
                        <p class="text-center text-[11px] text-[#86868b]">Today 9:41 AM</p>
                        <div class="flex justify-start">
                            <span class="max-w-[80%] rounded-[20px] rounded-bl-md bg-[#e9e9eb] px-3.5 py-2 text-[#1d1d1f]">📞 Missed call</span>
                        </div>
                        <div class="flex justify-end">
                            <span class="max-w-[82%] rounded-[20px] rounded-br-md bg-[#0a84ff] px-3.5 py-2 text-white">Sorry we missed you! Tap to book an appointment 👉 brightsmile.book</span>
                        </div>
                        <div class="flex justify-start">
                            <span class="max-w-[80%] rounded-[20px] rounded-bl-md bg-[#e9e9eb] px-3.5 py-2 text-[#1d1d1f]">Thursday afternoon please 🙌</span>
                        </div>
                        <div class="flex justify-end">
                            <span class="max-w-[82%] rounded-[20px] rounded-br-md bg-[#0a84ff] px-3.5 py-2 text-white">Booked you for Thu 2:30pm. See you then! ✅</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= BIG STATEMENT ================= --}}
    <section class="bg-[#f5f5f7]">
        <div class="mx-auto max-w-4xl px-5 py-24 text-center sm:py-32">
            <h2 class="text-4xl font-semibold leading-tight tracking-tight text-[#1d1d1f] sm:text-6xl">
                <span class="text-[#86868b]">62% of callers never call back.</span><br>
                So we answer for you — instantly.
            </h2>
            <div class="mt-7 flex flex-wrap items-center justify-center gap-x-7 gap-y-3 text-[17px] sm:text-xl">
                <a href="{{ route('register') }}" class="text-[#0066cc] hover:underline">Get started <span aria-hidden="true">&rsaquo;</span></a>
                <a href="{{ route('pricing') }}" class="text-[#0066cc] hover:underline">View pricing <span aria-hidden="true">&rsaquo;</span></a>
            </div>
        </div>
    </section>

    {{-- ================= PRODUCT TILES ================= --}}
    <section id="features" class="bg-white">
        <div class="mx-auto max-w-[1100px] px-3 py-3">
            <div class="grid gap-3 md:grid-cols-2">

                {{-- Instant text-back (light) --}}
                <div class="flex flex-col items-center overflow-hidden rounded-[18px] bg-[#f5f5f7] px-6 pt-12 text-center">
                    <h3 class="text-3xl font-semibold tracking-tight text-[#1d1d1f] sm:text-4xl">Instant text&#8209;back</h3>
                    <p class="mt-2 max-w-sm text-lg text-[#6e6e73]">The moment a call goes unanswered, we reply by SMS or WhatsApp — in seconds, 24/7.</p>
                    <a href="#" class="mt-3 text-[17px] text-[#0066cc] hover:underline">Learn more <span aria-hidden="true">&rsaquo;</span></a>
                    <div class="mt-9 w-full max-w-[260px] space-y-2 pb-10 text-left text-[13px]">
                        <div class="flex justify-start"><span class="rounded-2xl rounded-bl-md bg-white px-3 py-2 shadow-sm">📞 Missed call</span></div>
                        <div class="flex justify-end"><span class="rounded-2xl rounded-br-md bg-[#0a84ff] px-3 py-2 text-white shadow-sm">Sorry we missed you — book here →</span></div>
                    </div>
                </div>

                {{-- Revenue recovered (dark) --}}
                <div class="flex flex-col items-center overflow-hidden rounded-[18px] bg-black px-6 pt-12 text-center text-white">
                    <h3 class="text-3xl font-semibold tracking-tight sm:text-4xl">Revenue, recovered.</h3>
                    <p class="mt-2 max-w-sm text-lg text-white/70">See exactly how much you'd have lost — and how much LeadRecover wins back every month.</p>
                    <a href="{{ route('pricing') }}" class="mt-3 text-[17px] text-[#2997ff] hover:underline">See the numbers <span aria-hidden="true">&rsaquo;</span></a>
                    <div class="mt-10 w-full max-w-[280px] pb-10">
                        <div class="flex items-end justify-between gap-2">
                            @foreach([30,45,40,60,75,90] as $h)
                                <div class="flex-1 rounded-t-md bg-gradient-to-t from-[#0a84ff] to-[#34c759]" style="height: {{ $h }}px"></div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-2xl font-semibold">£5,295<span class="text-base font-normal text-white/60"> recovered</span></p>
                    </div>
                </div>

                {{-- Follow-ups (light) --}}
                <div class="flex flex-col items-center overflow-hidden rounded-[18px] bg-[#f5f5f7] px-6 pt-12 text-center">
                    <h3 class="text-3xl font-semibold tracking-tight text-[#1d1d1f] sm:text-4xl">Follow&#8209;ups on autopilot</h3>
                    <p class="mt-2 max-w-sm text-lg text-[#6e6e73]">No reply? We gently nudge at 1 hour and 24 hours, then bow out gracefully. Never pushy.</p>
                    <a href="#" class="mt-3 text-[17px] text-[#0066cc] hover:underline">Learn more <span aria-hidden="true">&rsaquo;</span></a>
                    <div class="mt-9 w-full max-w-[260px] space-y-3 pb-10 text-left">
                        @foreach(['Instant' => 'Sorry we missed your call!', '1 hour' => 'Still keen? Book a time →', '24 hours' => 'Last nudge — we\'re here when ready.'] as $when => $msg)
                            <div class="flex items-center gap-3">
                                <span class="w-14 shrink-0 text-right text-[11px] font-medium text-[#86868b]">{{ $when }}</span>
                                <span class="rounded-xl bg-white px-3 py-1.5 text-[12px] text-[#1d1d1f] shadow-sm">{{ $msg }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Booking built in (light) --}}
                <div class="flex flex-col items-center overflow-hidden rounded-[18px] bg-[#f5f5f7] px-6 pt-12 text-center">
                    <h3 class="text-3xl font-semibold tracking-tight text-[#1d1d1f] sm:text-4xl">Booking, built in</h3>
                    <p class="mt-2 max-w-sm text-lg text-[#6e6e73]">Connect Calendly, drop in your own link, or use our beautiful built-in booking page.</p>
                    <a href="#" class="mt-3 text-[17px] text-[#0066cc] hover:underline">Learn more <span aria-hidden="true">&rsaquo;</span></a>
                    <div class="mt-9 w-full max-w-[230px] rounded-2xl bg-white p-4 pb-10 shadow-sm">
                        <div class="grid grid-cols-7 gap-1.5">
                            @foreach(range(1,21) as $d)
                                <div class="flex aspect-square items-center justify-center rounded-md text-[11px] {{ $d === 11 ? 'bg-[#0a84ff] text-white' : 'bg-[#f5f5f7] text-[#86868b]' }}">{{ $d }}</div>
                            @endforeach
                        </div>
                        <p class="mt-3 text-left text-[12px] font-medium text-[#1d1d1f]">Thu 2:30pm · confirmed ✅</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= STATS ================= --}}
    <section class="bg-white">
        <div class="mx-auto max-w-5xl px-5 py-24 text-center sm:py-28">
            <h2 class="text-3xl font-semibold tracking-tight text-[#1d1d1f] sm:text-5xl">The numbers add up.</h2>
            <div class="mt-14 grid grid-cols-2 gap-y-12 sm:grid-cols-4">
                @foreach([['78%','choose whoever replies first'],['<60s','to an automatic reply'],['£1,200','avg. recovered / month'],['14 days','free, no card needed']] as $stat)
                    <div>
                        <p class="text-5xl font-semibold tracking-tight text-[#1d1d1f] sm:text-6xl">{{ $stat[0] }}</p>
                        <p class="mx-auto mt-2 max-w-[12rem] text-[15px] text-[#6e6e73]">{{ $stat[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= INDUSTRIES ================= --}}
    <section id="industries" class="bg-[#f5f5f7]">
        <div class="mx-auto max-w-4xl px-5 py-24 text-center sm:py-28">
            <h2 class="text-4xl font-semibold tracking-tight text-[#1d1d1f] sm:text-6xl">Built for local business.</h2>
            <p class="mx-auto mt-4 max-w-xl text-xl text-[#6e6e73]">If you book appointments and answer the phone, LeadRecover pays for itself.</p>
            <div class="mt-10 flex flex-wrap justify-center gap-2.5">
                @foreach($industries as $industry)
                    @if($industry['label'] !== 'Other')
                        <span class="rounded-full bg-white px-5 py-2 text-[15px] font-medium text-[#1d1d1f] shadow-sm">{{ $industry['label'] }}</span>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= FINAL CTA ================= --}}
    <section class="bg-white">
        <div class="mx-auto max-w-4xl px-5 py-24 text-center sm:py-32">
            <h2 class="text-4xl font-semibold tracking-tight text-[#1d1d1f] sm:text-6xl">Start recovering revenue today.</h2>
            <p class="mx-auto mt-4 max-w-xl text-xl text-[#6e6e73]">Free for {{ config('leadrecover.trial_days') }} days. No card required. Set up in minutes.</p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="rounded-full bg-[#0071e3] px-6 py-3 text-[17px] font-normal text-white transition hover:bg-[#0077ed]">Start free trial</a>
                <a href="{{ route('pricing') }}" class="text-[17px] text-[#0066cc] hover:underline">Compare plans <span aria-hidden="true">&rsaquo;</span></a>
            </div>
        </div>
    </section>
</x-layouts.marketing>
