@props(['class' => 'h-8'])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <span class="inline-flex {{ $class }} aspect-square items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-sm">
        <svg class="h-1/2 w-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6.5 3.5 9 4l1 4-2 1.5a12 12 0 0 0 5.5 5.5L15 14l4 1 .5 2.5a2 2 0 0 1-2 2.5A15 15 0 0 1 4 9a2 2 0 0 1 2.5-2Z"/>
            <path d="m14 8 6-6m0 0h-4m4 0v4"/>
        </svg>
    </span>
    <span class="text-lg font-bold tracking-tight text-slate-900">Lead<span class="text-brand-600">Recover</span></span>
</div>
