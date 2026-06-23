@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'LeadRecover — Turn missed calls into paying customers' }}</title>
    <meta name="description" content="LeadRecover automatically texts back missed callers so local businesses never lose another booking.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-slate-700 antialiased">
    <header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-100 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}"><x-brand /></a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
                <a href="{{ route('home') }}#features" class="hover:text-slate-900">Features</a>
                <a href="{{ route('home') }}#how" class="hover:text-slate-900">How it works</a>
                <a href="{{ route('home') }}#industries" class="hover:text-slate-900">Industries</a>
                <a href="{{ route('pricing') }}" class="hover:text-slate-900">Pricing</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Start free</a>
                @endauth
            </div>

            <button @click="open = !open" class="md:hidden" aria-label="Toggle menu">
                <svg class="h-6 w-6 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <div x-show="open" x-transition x-cloak class="border-t border-slate-100 md:hidden">
            <div class="space-y-1 px-6 py-4 text-sm font-medium text-slate-600">
                <a href="{{ route('home') }}#features" class="block py-1">Features</a>
                <a href="{{ route('pricing') }}" class="block py-1">Pricing</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="block py-1 text-brand-600">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block py-1">Log in</a>
                    <a href="{{ route('register') }}" class="block py-1 text-brand-600">Start free</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-100 bg-slate-50">
        <div class="mx-auto grid max-w-7xl gap-8 px-6 py-12 md:grid-cols-4">
            <div class="md:col-span-2">
                <x-brand />
                <p class="mt-3 max-w-sm text-sm text-slate-500">Turn missed calls into paying customers. Automated SMS &amp; WhatsApp recovery for local businesses.</p>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-900">Product</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-500">
                    <li><a href="{{ route('home') }}#features" class="hover:text-slate-900">Features</a></li>
                    <li><a href="{{ route('pricing') }}" class="hover:text-slate-900">Pricing</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-slate-900">Start free trial</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-900">Company</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-500">
                    <li><a href="#" class="hover:text-slate-900">Privacy</a></li>
                    <li><a href="#" class="hover:text-slate-900">Terms</a></li>
                    <li><a href="mailto:hello@leadrecover.app" class="hover:text-slate-900">Contact</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-100 py-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} LeadRecover. All rights reserved.
        </div>
    </footer>
</body>
</html>
