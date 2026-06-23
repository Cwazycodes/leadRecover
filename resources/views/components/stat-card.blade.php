@props(['label', 'value', 'sub' => null, 'tone' => 'brand'])

@php
    $tones = [
        'brand' => 'text-brand-600 bg-brand-50',
        'emerald' => 'text-emerald-600 bg-emerald-50',
        'amber' => 'text-amber-600 bg-amber-50',
        'sky' => 'text-sky-600 bg-sky-50',
        'rose' => 'text-rose-600 bg-rose-50',
    ];
    $tone = $tones[$tone] ?? $tones['brand'];
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
        <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $tone }}">
            {{ $icon ?? '' }}
        </span>
    </div>
    <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
    @if($sub)
        <p class="mt-1 text-xs text-slate-500">{{ $sub }}</p>
    @endif
</div>
