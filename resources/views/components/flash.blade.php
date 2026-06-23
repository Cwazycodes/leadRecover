@php
    $flashes = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        'error' => 'border-rose-200 bg-rose-50 text-rose-800',
    ];
@endphp

@foreach($flashes as $key => $classes)
    @if(session($key))
        <div x-data="{ show: true }" x-show="show" x-transition
             class="mb-4 flex items-start justify-between gap-3 rounded-xl border px-4 py-3 text-sm {{ $classes }}">
            <span>{{ session($key) }}</span>
            <button type="button" @click="show = false" class="shrink-0 opacity-60 hover:opacity-100">&times;</button>
        </div>
    @endif
@endforeach
