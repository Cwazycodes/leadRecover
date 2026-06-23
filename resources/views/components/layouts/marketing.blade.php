@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'LeadRecover — Turn missed calls into paying customers' }}</title>
    <meta name="description" content="LeadRecover automatically texts back missed callers so local businesses never lose another booking.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Apple-style system font stack for the marketing site. */
        .font-apple {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text",
                "Helvetica Neue", Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
        }
    </style>
</head>
<body class="font-apple min-h-screen bg-white text-[#1d1d1f] antialiased">
    {{-- Slim, translucent Apple-style nav --}}
    <header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-black/5 bg-white/70 backdrop-blur-xl backdrop-saturate-150">
        <div class="mx-auto flex h-12 max-w-5xl items-center justify-between px-5 text-[12px] text-[#1d1d1f]/90">
            <a href="{{ route('home') }}" class="font-semibold tracking-tight">Lead<span class="text-[#0066cc]">Recover</span></a>

            <nav class="hidden items-center gap-8 md:flex">
                <a href="{{ route('home') }}#overview" class="opacity-80 transition hover:opacity-100">Overview</a>
                <a href="{{ route('home') }}#features" class="opacity-80 transition hover:opacity-100">Features</a>
                <a href="{{ route('home') }}#industries" class="opacity-80 transition hover:opacity-100">Who it's for</a>
                <a href="{{ route('pricing') }}" class="opacity-80 transition hover:opacity-100">Pricing</a>
            </nav>

            <div class="hidden items-center gap-5 md:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="opacity-80 transition hover:opacity-100">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="opacity-80 transition hover:opacity-100">Sign in</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-[#0071e3] px-3 py-1 font-normal text-white transition hover:bg-[#0077ed]">Start free</a>
                @endauth
            </div>

            <button @click="open = !open" class="md:hidden" aria-label="Toggle menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>

        <div x-show="open" x-transition x-cloak class="border-t border-black/5 bg-white md:hidden">
            <div class="space-y-1 px-5 py-4 text-sm">
                <a href="{{ route('home') }}#features" class="block py-1.5">Features</a>
                <a href="{{ route('pricing') }}" class="block py-1.5">Pricing</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="block py-1.5 text-[#0066cc]">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block py-1.5">Sign in</a>
                    <a href="{{ route('register') }}" class="block py-1.5 text-[#0066cc]">Start free</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    {{-- Apple-style footnote footer --}}
    <footer class="bg-[#f5f5f7] text-[#6e6e73]">
        <div class="mx-auto max-w-5xl px-5 py-8 text-[12px] leading-relaxed">
            <p class="border-b border-black/10 pb-5">
                Turn missed calls into paying customers. LeadRecover sends automated SMS &amp; WhatsApp
                replies on your behalf; message delivery depends on your connected Twilio account and
                local carrier rules. Free trial requires no payment card.
            </p>

            <div class="grid grid-cols-2 gap-6 py-6 sm:grid-cols-4">
                <div>
                    <p class="mb-2 font-semibold text-[#1d1d1f]">Product</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}#features" class="hover:underline">Features</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:underline">Pricing</a></li>
                        <li><a href="{{ route('register') }}" class="hover:underline">Start free trial</a></li>
                    </ul>
                </div>
                <div>
                    <p class="mb-2 font-semibold text-[#1d1d1f]">Industries</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}#industries" class="hover:underline">Dentists &amp; clinics</a></li>
                        <li><a href="{{ route('home') }}#industries" class="hover:underline">Salons &amp; barbers</a></li>
                        <li><a href="{{ route('home') }}#industries" class="hover:underline">Estate agents</a></li>
                    </ul>
                </div>
                <div>
                    <p class="mb-2 font-semibold text-[#1d1d1f]">Company</p>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:underline">Privacy</a></li>
                        <li><a href="#" class="hover:underline">Terms</a></li>
                        <li><a href="mailto:hello@leadrecover.app" class="hover:underline">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <p class="mb-2 font-semibold text-[#1d1d1f]">Account</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('login') }}" class="hover:underline">Sign in</a></li>
                        <li><a href="{{ route('register') }}" class="hover:underline">Create account</a></li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col gap-2 border-t border-black/10 pt-5 sm:flex-row sm:items-center sm:justify-between">
                <p>Copyright &copy; {{ date('Y') }} LeadRecover. All rights reserved.</p>
                <p class="flex gap-4">
                    <a href="#" class="hover:underline">Privacy Policy</a>
                    <a href="#" class="hover:underline">Terms of Use</a>
                    <a href="{{ route('pricing') }}" class="hover:underline">Sales Policy</a>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
