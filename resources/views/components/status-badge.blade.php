@props(['status'])

@php
    $styles = [
        'new' => 'bg-sky-100 text-sky-700 ring-sky-600/20',
        'contacted' => 'bg-amber-100 text-amber-700 ring-amber-600/20',
        'booked' => 'bg-indigo-100 text-indigo-700 ring-indigo-600/20',
        'converted' => 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
        'lost' => 'bg-rose-100 text-rose-700 ring-rose-600/20',
        'stale' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
    ];
    $style = $styles[$status] ?? 'bg-slate-100 text-slate-600 ring-slate-500/20';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ring-1 ring-inset $style"]) }}>
    {{ $status }}
</span>
