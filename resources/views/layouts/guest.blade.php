<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-700 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-b from-brand-50 to-slate-100 px-6 py-10">
            <a href="{{ route('home') }}" class="mb-6">
                <x-brand class="h-10" />
            </a>

            <div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white px-6 py-8 shadow-sm sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
