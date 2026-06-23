<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book with {{ $business->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-brand-50 to-white font-sans text-slate-700 antialiased">
    <div class="mx-auto flex min-h-screen max-w-lg flex-col justify-center px-6 py-12">
        <div class="mb-6 text-center">
            @if($business->logo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($business->logo_path) }}" alt="{{ $business->name }}" class="mx-auto mb-4 h-16 w-16 rounded-2xl object-cover shadow">
            @endif
            <h1 class="text-2xl font-bold text-slate-900">Book with {{ $business->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Choose a time and we'll confirm your appointment.</p>
        </div>

        @if(session('booked'))
            <div class="rounded-2xl border border-emerald-200 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                </div>
                <h2 class="mt-4 text-lg font-semibold text-slate-900">Request received!</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $business->name }} will be in touch shortly to confirm your appointment.</p>
            </div>
        @else
            <form method="POST" action="{{ route('book.store', $business) }}" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">Your name</label>
                    <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Phone</label>
                    <input name="phone" value="{{ old('phone') }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('phone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email <span class="text-slate-400">(optional)</span></label>
                    <input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Preferred date &amp; time</label>
                    <input name="booking_date" type="datetime-local" value="{{ old('booking_date') }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('booking_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Anything we should know? <span class="text-slate-400">(optional)</span></label>
                    <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">{{ old('notes') }}</textarea>
                </div>
                <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Request appointment</button>
            </form>
        @endif

        <p class="mt-6 text-center text-xs text-slate-400">Powered by <span class="font-semibold text-slate-500">LeadRecover</span></p>
    </div>
</body>
</html>
