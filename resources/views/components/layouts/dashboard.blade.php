@props(['title' => null, 'header' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} · LeadRecover</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-700 antialiased">
@php
    $currentBusiness ??= auth()->user()?->business;
@endphp
<div x-data="{ sidebar: false }" class="min-h-screen lg:flex">

    {{-- Sidebar --}}
    <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"></div>

    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-0 transform border-r border-slate-200 bg-white transition lg:static lg:translate-x-0">
        <div class="flex h-16 items-center border-b border-slate-100 px-6">
            <a href="{{ Auth::user()->isPlatformAdmin() ? route('admin.dashboard') : route('dashboard') }}"><x-brand /></a>
        </div>

        <nav class="flex flex-col gap-1 p-4 text-sm font-medium">
            @php
                $item = 'flex items-center gap-3 rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900';
                $active = 'flex items-center gap-3 rounded-lg bg-brand-50 px-3 py-2 font-semibold text-brand-700';
            @endphp

            @if(isset($currentBusiness))
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? $active : $item }}">
                    <x-icon.grid /> Dashboard
                </a>
                <a href="{{ route('leads.index') }}" class="{{ request()->routeIs('leads.*') ? $active : $item }}">
                    <x-icon.users /> Leads
                </a>
                <a href="{{ route('settings.edit') }}" class="{{ request()->routeIs('settings.*') ? $active : $item }}">
                    <x-icon.cog /> Settings
                </a>
                <a href="{{ route('billing.index') }}" class="{{ request()->routeIs('billing.*') ? $active : $item }}">
                    <x-icon.card /> Billing
                </a>
            @endif

            @if(Auth::user()->isPlatformAdmin())
                <p class="px-3 pb-1 pt-5 text-xs font-semibold uppercase tracking-wider text-slate-400">Platform</p>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? $active : $item }}">
                    <x-icon.grid /> Overview
                </a>
                <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? $active : $item }}">
                    <x-icon.building /> Customers
                </a>
            @endif
        </nav>

        @isset($currentBusiness)
            <div class="absolute inset-x-0 bottom-0 border-t border-slate-100 p-4">
                <a href="{{ route('book', $currentBusiness) }}" target="_blank"
                   class="block rounded-lg bg-slate-900 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-slate-800">
                    View booking page →
                </a>
            </div>
        @endisset
    </aside>

    {{-- Main column --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 backdrop-blur sm:px-6">
            <div class="flex items-center gap-3">
                <button @click="sidebar = true" class="lg:hidden" aria-label="Open sidebar">
                    <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-base font-semibold text-slate-900">{{ $header ?? ($title ?? 'Dashboard') }}</h1>
            </div>

            <div class="flex items-center gap-2">
                {{-- Notification bell --}}
                @php $unread = Auth::user()->unreadNotifications; @endphp
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Notifications">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 0 1-6 0v-1"/></svg>
                        @if($unread->count())
                            <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">{{ $unread->count() }}</span>
                        @endif
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition
                         class="absolute right-0 mt-2 w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                            <p class="text-sm font-semibold text-slate-900">Notifications</p>
                            @if($unread->count())
                                <form method="POST" action="{{ route('notifications.read') }}">@csrf
                                    <button class="text-xs font-medium text-brand-600 hover:underline">Mark all read</button>
                                </form>
                            @endif
                        </div>
                        <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                            @forelse($unread->take(8) as $n)
                                <a href="{{ $n->data['url'] ?? '#' }}" class="block px-4 py-3 hover:bg-slate-50">
                                    <p class="text-sm font-medium text-slate-900">{{ $n->data['title'] ?? 'Notification' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $n->data['message'] ?? '' }}</p>
                                    <p class="mt-1 text-[11px] text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <p class="px-4 py-8 text-center text-sm text-slate-400">You're all caught up 🎉</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- User menu --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-slate-100">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ Auth::user()->name }}</span>
                        <svg class="hidden h-4 w-4 text-slate-400 sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Profile</a>
                        @isset($currentBusiness)
                            <a href="{{ route('billing.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">Billing</a>
                        @endisset
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="block w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-slate-50">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <x-flash />
            {{ $slot }}
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
